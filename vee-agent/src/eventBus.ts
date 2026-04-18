import { EventEmitter } from "node:events";

export type LogEvent = {
  id: string;
  timestamp: string;
  session_id: string;
  mode: "chat" | "cowork";
  type: "tool_start" | "tool_result" | "tool_error" | "message_start" | "message_done";
  tool_name?: string;
  args?: unknown;
  result?: unknown;
  error?: string;
  duration_ms?: number;
};

class EventBus extends EventEmitter {
  emitLog(data: LogEvent): boolean {
    return super.emit("log", data);
  }

  onLog(listener: (data: LogEvent) => void): this {
    return super.on("log", listener as (...args: unknown[]) => void);
  }

  offLog(listener: (data: LogEvent) => void): this {
    return super.off("log", listener as (...args: unknown[]) => void);
  }
}

export const eventBus = new EventBus();
eventBus.setMaxListeners(100);

export function emitLog(data: LogEvent): void {
  eventBus.emitLog(data);
}
