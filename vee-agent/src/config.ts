import "dotenv/config";

export const PORT = Number.parseInt(process.env.PORT ?? "3334", 10);
export const HOST = process.env.HOST ?? "127.0.0.1";
export const AUTH_TOKEN = (process.env.AGENT_AUTH_TOKEN ?? "").trim();

export const OPENAI_API_KEY = (process.env.OPENAI_API_KEY ?? "").trim();
export const MODEL_CHAT = (process.env.MODEL_CHAT ?? "gpt-5.4-mini").trim();
export const MODEL_COWORK = (process.env.MODEL_COWORK ?? "gpt-5.3-codex").trim();

export const MCP_SERVER_URL = (process.env.MCP_SERVER_URL ?? "http://localhost:3333/mcp").trim();
export const MCP_AUTH_TOKEN = (process.env.MCP_AUTH_TOKEN ?? "").trim();

export const DB_HOST = (process.env.DB_HOST ?? "127.0.0.1").trim();
export const DB_PORT = Number.parseInt(process.env.DB_PORT ?? "3306", 10);
export const DB_USER = (process.env.DB_USER ?? "").trim();
export const DB_PASSWORD = process.env.DB_PASSWORD ?? "";
export const DB_DATABASE = (process.env.DB_DATABASE ?? "").trim();

export const VEE_NAME = (process.env.VEE_NAME ?? "Vee").trim();

export const SYSTEM_PROMPT_CHAT = `Você é ${VEE_NAME}, a assistente de inteligência da MM Criativos.

No modo Chat você é reflexiva, estratégica e conversacional. Você ajuda a planejar, organizar ideias, tomar decisões e fazer brainstorming. Você conhece profundamente o ecossistema da MM Criativos: projetos, clientes, automações n8n, infraestrutura e banco de dados.

Seja direta, honesta e objetiva. Quando não souber algo, diga. Quando precisar de contexto, pergunte. Responda em português brasileiro, no mesmo tom casual e profissional de Marcus.`;

export const SYSTEM_PROMPT_COWORK = `Você é ${VEE_NAME}, a assistente operacional da MM Criativos.

No modo Cowork você é executora. Use as ferramentas disponíveis para agir no sistema: consultar dados, executar workflows, verificar servidor, criar notas no Obsidian, registrar tarefas e incidentes. Sempre confirme antes de ações destrutivas ou irreversíveis.

Quando usar uma ferramenta, explique brevemente o que está fazendo. Seja precisa e eficiente. Responda em português brasileiro.`;
