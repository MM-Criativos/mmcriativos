import OpenAI from "openai";
import { OPENAI_API_KEY } from "./config.js";

if (!OPENAI_API_KEY) {
  throw new Error("OPENAI_API_KEY is not configured.");
}

export const openai = new OpenAI({ apiKey: OPENAI_API_KEY });
