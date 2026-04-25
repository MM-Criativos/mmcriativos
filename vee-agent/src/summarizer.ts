import { openai } from "./openaiClient.js";
import type { StoredMessage } from "./sessions.js";

// Model used for summarization — cheaper than the main models, sufficient for this task
const SUMMARY_MODEL = (process.env.SUMMARY_MODEL ?? "gpt-4.1-mini").trim();
const SUMMARY_MAX_TOKENS = Number.parseInt(process.env.SUMMARY_MAX_TOKENS ?? "1200", 10);
// Max chars per message included in the summarization prompt (avoids sending huge tool results)
const SUMMARY_MSG_PREVIEW_CHARS = 800;

/**
 * Calls OpenAI to summarize a list of old conversation turns.
 * If a previous summary exists it is incorporated (incremental update).
 */
export async function generateSummary(
  turns: StoredMessage[],
  existingSummary: string | null
): Promise<string> {
  // Only include user and assistant text — tool results add noise to the summary
  const relevant = turns.filter(
    (r) => (r.role === "user" || r.role === "assistant") && r.content
  );

  const conversationText = relevant
    .map((r) => {
      const speaker = r.role === "user" ? "Usuário" : "Vee";
      const text = (r.content ?? "").slice(0, SUMMARY_MSG_PREVIEW_CHARS);
      return `${speaker}: ${text}`;
    })
    .join("\n\n");

  const prompt = existingSummary
    ? `Resumo anterior da sessão:\n${existingSummary}\n\n---\nNova conversa a incorporar ao resumo:\n${conversationText}`
    : conversationText;

  const response = await openai.chat.completions.create({
    model: SUMMARY_MODEL,
    messages: [
      {
        role: "system",
        content:
          "Você é um assistente técnico. Crie um resumo conciso (máx. 600 palavras) " +
          "da conversa em português, preservando: decisões técnicas tomadas, contexto " +
          "operacional relevante, tarefas discutidas, problemas identificados e o que " +
          "foi resolvido. Use bullet points quando útil."
      },
      { role: "user", content: prompt }
    ],
    max_tokens: SUMMARY_MAX_TOKENS
  });

  return response.choices[0]?.message?.content ?? "";
}
