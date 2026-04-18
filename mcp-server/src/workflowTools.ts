export type WorkflowRecord = Record<string, unknown>;

type WorkflowNodeRecord = Record<string, unknown>;
type ConnectionEntryRecord = Record<string, unknown>;

export type WorkflowPatchOperation =
  | {
      op: "add_node";
      node: WorkflowNodeRecord;
    }
  | {
      op: "remove_node";
      nodeName: string;
    }
  | {
      op: "update_node";
      nodeName: string;
      patch: WorkflowNodeRecord;
    }
  | {
      op: "update_node_parameters";
      nodeName: string;
      parameters: Record<string, unknown>;
      merge?: boolean;
    }
  | {
      op: "rename_node";
      nodeName: string;
      newName: string;
    }
  | {
      op: "upsert_connection";
      fromNode: string;
      toNode: string;
      outputIndex?: number;
      inputIndex?: number;
      connectionType?: string;
    }
  | {
      op: "remove_connection";
      fromNode: string;
      toNode: string;
      outputIndex?: number;
      inputIndex?: number;
      connectionType?: string;
    };

export type AppliedOperationSummary = {
  op: WorkflowPatchOperation["op"];
  detail: string;
};

export type WorkflowDiffSummary = {
  has_changes: boolean;
  workflow: {
    name_changed: boolean;
    active_changed: boolean;
  };
  nodes: {
    before_count: number;
    after_count: number;
    added: string[];
    removed: string[];
    changed: string[];
  };
  connections: {
    before_count: number;
    after_count: number;
    added_count: number;
    removed_count: number;
    added_samples: string[];
    removed_samples: string[];
  };
};

const DEFAULT_CONNECTION_TYPE = "main";

function isRecord(value: unknown): value is Record<string, unknown> {
  return !!value && typeof value === "object" && !Array.isArray(value);
}

function cloneWorkflow(workflow: WorkflowRecord): WorkflowRecord {
  return structuredClone(workflow);
}

function asString(value: unknown, fieldName: string): string {
  if (typeof value !== "string" || value.trim() === "") {
    throw new Error(`${fieldName} must be a non-empty string.`);
  }
  return value.trim();
}

function asNonNegativeInteger(value: unknown, fieldName: string, defaultValue: number): number {
  if (value === undefined || value === null) {
    return defaultValue;
  }

  if (typeof value !== "number" || !Number.isInteger(value) || value < 0) {
    throw new Error(`${fieldName} must be a non-negative integer.`);
  }

  return value;
}

function asRecord(value: unknown, fieldName: string): Record<string, unknown> {
  if (!isRecord(value)) {
    throw new Error(`${fieldName} must be an object.`);
  }
  return structuredClone(value);
}

function getNodesMutable(workflow: WorkflowRecord): WorkflowNodeRecord[] {
  const raw = workflow.nodes;
  if (!Array.isArray(raw)) {
    throw new Error("Workflow payload is invalid: 'nodes' must be an array.");
  }

  const nodes = raw as unknown[];
  for (const [index, node] of nodes.entries()) {
    if (!isRecord(node)) {
      throw new Error(`Workflow payload is invalid: node at index ${index} is not an object.`);
    }
    const nodeName = node.name;
    if (typeof nodeName !== "string" || nodeName.trim() === "") {
      throw new Error(`Workflow payload is invalid: node at index ${index} has no valid 'name'.`);
    }
  }

  return nodes as WorkflowNodeRecord[];
}

function getConnectionsMutable(workflow: WorkflowRecord): Record<string, unknown> {
  const raw = workflow.connections;
  if (raw === undefined || raw === null) {
    workflow.connections = {};
    return workflow.connections as Record<string, unknown>;
  }

  if (!isRecord(raw)) {
    throw new Error("Workflow payload is invalid: 'connections' must be an object.");
  }

  return raw;
}

function findNodeIndexByName(nodes: WorkflowNodeRecord[], nodeName: string): number {
  return nodes.findIndex((node) => String(node.name).trim() === nodeName);
}

function assertNodeExists(nodes: WorkflowNodeRecord[], nodeName: string, fieldName = "nodeName"): void {
  if (findNodeIndexByName(nodes, nodeName) < 0) {
    throw new Error(`${fieldName} '${nodeName}' was not found in workflow nodes.`);
  }
}

function renameNodeReferencesInConnections(
  connections: Record<string, unknown>,
  previousName: string,
  nextName: string
): void {
  if (Object.prototype.hasOwnProperty.call(connections, previousName)) {
    connections[nextName] = connections[previousName];
    delete connections[previousName];
  }

  for (const fromNode of Object.keys(connections)) {
    const fromNodeConnectionMap = connections[fromNode];
    if (!isRecord(fromNodeConnectionMap)) {
      continue;
    }

    for (const connectionType of Object.keys(fromNodeConnectionMap)) {
      const outputs = fromNodeConnectionMap[connectionType];
      if (!Array.isArray(outputs)) {
        continue;
      }

      for (const lane of outputs) {
        if (!Array.isArray(lane)) {
          continue;
        }

        for (const entry of lane) {
          if (!isRecord(entry)) {
            continue;
          }

          if (entry.node === previousName) {
            entry.node = nextName;
          }
        }
      }
    }
  }
}

function getConnectionLaneMutable(args: {
  connections: Record<string, unknown>;
  fromNode: string;
  connectionType: string;
  outputIndex: number;
}): ConnectionEntryRecord[] {
  const { connections, fromNode, connectionType, outputIndex } = args;

  const existingFromNodeMap = connections[fromNode];
  const fromNodeMap: Record<string, unknown> = isRecord(existingFromNodeMap)
    ? existingFromNodeMap
    : {};
  if (!isRecord(existingFromNodeMap)) {
    connections[fromNode] = fromNodeMap;
  }

  let byType = fromNodeMap[connectionType];
  if (!Array.isArray(byType)) {
    byType = [];
    fromNodeMap[connectionType] = byType;
  }

  const outputs = byType as unknown[];
  while (outputs.length <= outputIndex) {
    outputs.push([]);
  }

  let lane = outputs[outputIndex];
  if (!Array.isArray(lane)) {
    lane = [];
    outputs[outputIndex] = lane;
  }

  return lane as ConnectionEntryRecord[];
}

function removeNodeFromIncomingConnections(
  connections: Record<string, unknown>,
  nodeName: string
): void {
  for (const fromNode of Object.keys(connections)) {
    const fromNodeConnectionMap = connections[fromNode];
    if (!isRecord(fromNodeConnectionMap)) {
      continue;
    }

    for (const connectionType of Object.keys(fromNodeConnectionMap)) {
      const outputs = fromNodeConnectionMap[connectionType];
      if (!Array.isArray(outputs)) {
        continue;
      }

      outputs.forEach((lane, index) => {
        if (!Array.isArray(lane)) {
          return;
        }

        const filteredLane = lane.filter((entry) => !isRecord(entry) || entry.node !== nodeName);
        outputs[index] = filteredLane;
      });
    }
  }
}

export function parseWorkflowOperations(raw: unknown): WorkflowPatchOperation[] {
  if (!Array.isArray(raw) || raw.length === 0) {
    throw new Error("operations must be a non-empty array.");
  }

  return raw.map((entry, index) => {
    if (!isRecord(entry)) {
      throw new Error(`operations[${index}] must be an object.`);
    }

    const op = asString(entry.op, `operations[${index}].op`) as WorkflowPatchOperation["op"];

    switch (op) {
      case "add_node":
        return {
          op,
          node: asRecord(entry.node, `operations[${index}].node`)
        };
      case "remove_node":
        return {
          op,
          nodeName: asString(entry.nodeName, `operations[${index}].nodeName`)
        };
      case "update_node":
        return {
          op,
          nodeName: asString(entry.nodeName, `operations[${index}].nodeName`),
          patch: asRecord(entry.patch, `operations[${index}].patch`)
        };
      case "update_node_parameters":
        return {
          op,
          nodeName: asString(entry.nodeName, `operations[${index}].nodeName`),
          parameters: asRecord(entry.parameters, `operations[${index}].parameters`),
          merge: typeof entry.merge === "boolean" ? entry.merge : undefined
        };
      case "rename_node":
        return {
          op,
          nodeName: asString(entry.nodeName, `operations[${index}].nodeName`),
          newName: asString(entry.newName, `operations[${index}].newName`)
        };
      case "upsert_connection":
      case "remove_connection":
        return {
          op,
          fromNode: asString(entry.fromNode, `operations[${index}].fromNode`),
          toNode: asString(entry.toNode, `operations[${index}].toNode`),
          outputIndex: asNonNegativeInteger(
            entry.outputIndex,
            `operations[${index}].outputIndex`,
            0
          ),
          inputIndex: asNonNegativeInteger(entry.inputIndex, `operations[${index}].inputIndex`, 0),
          connectionType:
            entry.connectionType === undefined
              ? undefined
              : asString(entry.connectionType, `operations[${index}].connectionType`)
        };
      default:
        throw new Error(`operations[${index}].op '${String(entry.op)}' is not supported.`);
    }
  });
}

export function applyWorkflowOperations(
  sourceWorkflow: WorkflowRecord,
  operations: WorkflowPatchOperation[]
): { workflow: WorkflowRecord; applied: AppliedOperationSummary[] } {
  const workflow = cloneWorkflow(sourceWorkflow);
  const nodes = getNodesMutable(workflow);
  const connections = getConnectionsMutable(workflow);
  const applied: AppliedOperationSummary[] = [];

  for (const operation of operations) {
    switch (operation.op) {
      case "add_node": {
        const node = structuredClone(operation.node);
        const nodeName = asString(node.name, "node.name");
        if (findNodeIndexByName(nodes, nodeName) >= 0) {
          throw new Error(`Cannot add node '${nodeName}': a node with this name already exists.`);
        }

        nodes.push(node);
        applied.push({
          op: "add_node",
          detail: `Added node '${nodeName}'.`
        });
        break;
      }
      case "remove_node": {
        const nodeName = operation.nodeName;
        const nodeIndex = findNodeIndexByName(nodes, nodeName);
        if (nodeIndex < 0) {
          throw new Error(`Cannot remove node '${nodeName}': node not found.`);
        }

        nodes.splice(nodeIndex, 1);
        delete connections[nodeName];
        removeNodeFromIncomingConnections(connections, nodeName);
        applied.push({
          op: "remove_node",
          detail: `Removed node '${nodeName}' and related connections.`
        });
        break;
      }
      case "update_node": {
        const nodeName = operation.nodeName;
        const nodeIndex = findNodeIndexByName(nodes, nodeName);
        if (nodeIndex < 0) {
          throw new Error(`Cannot update node '${nodeName}': node not found.`);
        }

        const patch = structuredClone(operation.patch);
        if (typeof patch.name === "string" && patch.name.trim() !== "" && patch.name !== nodeName) {
          throw new Error("Use 'rename_node' operation to rename nodes.");
        }

        nodes[nodeIndex] = {
          ...nodes[nodeIndex],
          ...patch
        };

        applied.push({
          op: "update_node",
          detail: `Updated node '${nodeName}'.`
        });
        break;
      }
      case "update_node_parameters": {
        const nodeName = operation.nodeName;
        const nodeIndex = findNodeIndexByName(nodes, nodeName);
        if (nodeIndex < 0) {
          throw new Error(`Cannot update parameters for node '${nodeName}': node not found.`);
        }

        const node = nodes[nodeIndex];
        const currentParameters = isRecord(node.parameters) ? node.parameters : {};
        const nextParameters =
          operation.merge === false
            ? structuredClone(operation.parameters)
            : { ...currentParameters, ...structuredClone(operation.parameters) };

        node.parameters = nextParameters;
        applied.push({
          op: "update_node_parameters",
          detail: `Updated parameters for node '${nodeName}'.`
        });
        break;
      }
      case "rename_node": {
        const nodeName = operation.nodeName;
        const newName = operation.newName;
        const nodeIndex = findNodeIndexByName(nodes, nodeName);
        if (nodeIndex < 0) {
          throw new Error(`Cannot rename node '${nodeName}': node not found.`);
        }
        if (findNodeIndexByName(nodes, newName) >= 0) {
          throw new Error(`Cannot rename node '${nodeName}' to '${newName}': target name exists.`);
        }

        nodes[nodeIndex].name = newName;
        renameNodeReferencesInConnections(connections, nodeName, newName);
        applied.push({
          op: "rename_node",
          detail: `Renamed node '${nodeName}' to '${newName}'.`
        });
        break;
      }
      case "upsert_connection": {
        assertNodeExists(nodes, operation.fromNode, "fromNode");
        assertNodeExists(nodes, operation.toNode, "toNode");
        const outputIndex = operation.outputIndex ?? 0;
        const inputIndex = operation.inputIndex ?? 0;
        const connectionType = (operation.connectionType ?? DEFAULT_CONNECTION_TYPE).trim();
        if (!connectionType) {
          throw new Error("connectionType cannot be empty.");
        }

        const lane = getConnectionLaneMutable({
          connections,
          fromNode: operation.fromNode,
          connectionType,
          outputIndex
        });

        const existingIndex = lane.findIndex((entry) => {
          if (!isRecord(entry)) {
            return false;
          }

          return (
            entry.node === operation.toNode &&
            entry.type === connectionType &&
            Number(entry.index ?? 0) === inputIndex
          );
        });

        if (existingIndex < 0) {
          lane.push({
            node: operation.toNode,
            type: connectionType,
            index: inputIndex
          });
        }

        applied.push({
          op: "upsert_connection",
          detail: `Upserted connection '${operation.fromNode}' -> '${operation.toNode}' (${connectionType}:${outputIndex}).`
        });
        break;
      }
      case "remove_connection": {
        const outputIndex = operation.outputIndex ?? 0;
        const inputIndex = operation.inputIndex ?? 0;
        const connectionType = (operation.connectionType ?? DEFAULT_CONNECTION_TYPE).trim();
        if (!connectionType) {
          throw new Error("connectionType cannot be empty.");
        }

        const fromNodeMap = connections[operation.fromNode];
        if (!isRecord(fromNodeMap)) {
          applied.push({
            op: "remove_connection",
            detail: `No connection map found for '${operation.fromNode}'.`
          });
          break;
        }

        const outputs = fromNodeMap[connectionType];
        if (!Array.isArray(outputs) || !Array.isArray(outputs[outputIndex])) {
          applied.push({
            op: "remove_connection",
            detail: `No connection lane found for '${operation.fromNode}' (${connectionType}:${outputIndex}).`
          });
          break;
        }

        const lane = outputs[outputIndex] as unknown[];
        const filtered = lane.filter((entry) => {
          if (!isRecord(entry)) {
            return true;
          }

          return !(
            entry.node === operation.toNode &&
            entry.type === connectionType &&
            Number(entry.index ?? 0) === inputIndex
          );
        });
        outputs[outputIndex] = filtered;

        applied.push({
          op: "remove_connection",
          detail: `Removed connection '${operation.fromNode}' -> '${operation.toNode}' (${connectionType}:${outputIndex}).`
        });
        break;
      }
      default: {
        const unsupportedOperation: never = operation;
        throw new Error(`Unsupported operation: ${JSON.stringify(unsupportedOperation)}`);
      }
    }
  }

  workflow.nodes = nodes;
  workflow.connections = connections;
  return { workflow, applied };
}

function stableSerialize(value: unknown): string {
  if (Array.isArray(value)) {
    return `[${value.map((item) => stableSerialize(item)).join(",")}]`;
  }

  if (isRecord(value)) {
    const keys = Object.keys(value).sort((a, b) => a.localeCompare(b));
    return `{${keys.map((key) => `${JSON.stringify(key)}:${stableSerialize(value[key])}`).join(",")}}`;
  }

  return JSON.stringify(value);
}

function extractNodesByName(workflow: WorkflowRecord): Map<string, WorkflowNodeRecord> {
  const map = new Map<string, WorkflowNodeRecord>();
  const raw = workflow.nodes;
  if (!Array.isArray(raw)) {
    return map;
  }

  for (const node of raw) {
    if (!isRecord(node) || typeof node.name !== "string" || node.name.trim() === "") {
      continue;
    }

    map.set(node.name.trim(), node);
  }

  return map;
}

function flattenConnectionSignatures(workflow: WorkflowRecord): Set<string> {
  const signatures = new Set<string>();
  const rawConnections = workflow.connections;

  if (!isRecord(rawConnections)) {
    return signatures;
  }

  for (const [fromNode, fromNodeConnectionMap] of Object.entries(rawConnections)) {
    if (!isRecord(fromNodeConnectionMap)) {
      continue;
    }

    for (const [connectionType, outputs] of Object.entries(fromNodeConnectionMap)) {
      if (!Array.isArray(outputs)) {
        continue;
      }

      outputs.forEach((lane, outputIndex) => {
        if (!Array.isArray(lane)) {
          return;
        }

        lane.forEach((entry) => {
          if (!isRecord(entry)) {
            return;
          }

          const toNode = typeof entry.node === "string" ? entry.node.trim() : "";
          if (!toNode) {
            return;
          }

          const inputIndex =
            typeof entry.index === "number" && Number.isInteger(entry.index) ? entry.index : 0;

          signatures.add(
            `${fromNode}|${connectionType}|${String(outputIndex)}|${toNode}|${String(inputIndex)}`
          );
        });
      });
    }
  }

  return signatures;
}

function limitArray(values: string[], limit = 20): string[] {
  return values.slice(0, limit);
}

export function summarizeWorkflowDiff(
  beforeWorkflow: WorkflowRecord,
  afterWorkflow: WorkflowRecord
): WorkflowDiffSummary {
  const beforeName = typeof beforeWorkflow.name === "string" ? beforeWorkflow.name : null;
  const afterName = typeof afterWorkflow.name === "string" ? afterWorkflow.name : null;
  const beforeActive =
    typeof beforeWorkflow.active === "boolean" ? beforeWorkflow.active : beforeWorkflow.active ?? null;
  const afterActive =
    typeof afterWorkflow.active === "boolean" ? afterWorkflow.active : afterWorkflow.active ?? null;

  const beforeNodesMap = extractNodesByName(beforeWorkflow);
  const afterNodesMap = extractNodesByName(afterWorkflow);

  const addedNodes = [...afterNodesMap.keys()].filter((name) => !beforeNodesMap.has(name));
  const removedNodes = [...beforeNodesMap.keys()].filter((name) => !afterNodesMap.has(name));

  const changedNodes: string[] = [];
  for (const [name, beforeNode] of beforeNodesMap.entries()) {
    const afterNode = afterNodesMap.get(name);
    if (!afterNode) {
      continue;
    }

    if (stableSerialize(beforeNode) !== stableSerialize(afterNode)) {
      changedNodes.push(name);
    }
  }

  const beforeConnections = flattenConnectionSignatures(beforeWorkflow);
  const afterConnections = flattenConnectionSignatures(afterWorkflow);

  const addedConnections = [...afterConnections].filter((signature) => !beforeConnections.has(signature));
  const removedConnections = [...beforeConnections].filter(
    (signature) => !afterConnections.has(signature)
  );

  const hasChanges =
    beforeName !== afterName ||
    beforeActive !== afterActive ||
    addedNodes.length > 0 ||
    removedNodes.length > 0 ||
    changedNodes.length > 0 ||
    addedConnections.length > 0 ||
    removedConnections.length > 0;

  return {
    has_changes: hasChanges,
    workflow: {
      name_changed: beforeName !== afterName,
      active_changed: beforeActive !== afterActive
    },
    nodes: {
      before_count: beforeNodesMap.size,
      after_count: afterNodesMap.size,
      added: limitArray(addedNodes.sort((a, b) => a.localeCompare(b))),
      removed: limitArray(removedNodes.sort((a, b) => a.localeCompare(b))),
      changed: limitArray(changedNodes.sort((a, b) => a.localeCompare(b)))
    },
    connections: {
      before_count: beforeConnections.size,
      after_count: afterConnections.size,
      added_count: addedConnections.length,
      removed_count: removedConnections.length,
      added_samples: limitArray(addedConnections.sort((a, b) => a.localeCompare(b))),
      removed_samples: limitArray(removedConnections.sort((a, b) => a.localeCompare(b)))
    }
  };
}
