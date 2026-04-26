import "dotenv/config";

export const PORT = Number.parseInt(process.env.PORT ?? "3334", 10);
export const HOST = process.env.HOST ?? "127.0.0.1";
export const AUTH_TOKEN = (process.env.AGENT_AUTH_TOKEN ?? "").trim();

export const OPENAI_API_KEY = (process.env.OPENAI_API_KEY ?? "").trim();
export const MODEL_CHAT = (process.env.MODEL_CHAT ?? "gpt-5.4-mini").trim();
export const MODEL_COWORK = (process.env.MODEL_COWORK ?? "gpt-5.4-mini").trim();
// MODEL_EXECUTE: execution model after tool results. Defaults to MODEL_COWORK.
// Set MODEL_EXECUTE=gpt-5.3-codex in env for the planner/executor split.
export const MODEL_EXECUTE = (process.env.MODEL_EXECUTE ?? MODEL_COWORK).trim();

export const MCP_SERVER_URL = (process.env.MCP_SERVER_URL ?? "http://localhost:3333/mcp").trim();
export const MCP_AUTH_TOKEN = (process.env.MCP_AUTH_TOKEN ?? "").trim();

export const DB_HOST = (process.env.DB_HOST ?? "127.0.0.1").trim();
export const DB_PORT = Number.parseInt(process.env.DB_PORT ?? "3306", 10);
export const DB_USER = (process.env.DB_USER ?? "").trim();
export const DB_PASSWORD = process.env.DB_PASSWORD ?? "";
export const DB_DATABASE = (process.env.DB_DATABASE ?? "").trim();

export const VEE_NAME = (process.env.VEE_NAME ?? "Vee").trim();
export const VEE_CONTROL_BASE_URL = (process.env.VEE_CONTROL_BASE_URL ?? "").trim();
export const VEE_CONTROL_TOKEN = (process.env.VEE_CONTROL_TOKEN ?? "").trim();
export const VEE_CONTROL_TIMEOUT_MS = Number.parseInt(process.env.VEE_CONTROL_TIMEOUT_MS ?? "20000", 10);

export const SYSTEM_PROMPT_CHAT = `Voce e ${VEE_NAME}, a assistente de inteligencia da MM Criativos.

No modo Chat voce e reflexiva, estrategica e conversacional. Voce ajuda a planejar, organizar ideias, tomar decisoes e fazer brainstorming. Voce conhece profundamente o ecossistema da MM Criativos: projetos, clientes, automacoes n8n, infraestrutura e banco de dados.

Seja direta, honesta e objetiva. Quando nao souber algo, diga. Quando precisar de contexto, pergunte. Responda em portugues brasileiro, no mesmo tom casual e profissional de Marcus.`;

export const SYSTEM_PROMPT_COWORK = `Voce e ${VEE_NAME}, a assistente operacional da MM Criativos.

No modo Cowork voce e executora. Use as ferramentas disponiveis para agir no sistema: consultar dados, executar workflows, verificar servidor, criar notas no Obsidian, registrar tarefas e incidentes. Sempre confirme antes de acoes destrutivas ou irreversiveis.

Quando usar uma ferramenta, explique brevemente o que esta fazendo. Seja precisa e eficiente. Responda em portugues brasileiro.`;
