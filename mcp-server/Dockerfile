# ─── Stage 1: Builder ─────────────────────────────────────────────────────────
FROM node:20-alpine AS builder

# Build tools needed for ssh2 native bindings (cpu-features)
RUN apk add --no-cache python3 make g++

WORKDIR /app

# Install all dependencies (including dev for TypeScript build)
COPY package*.json ./
RUN npm ci

# Build TypeScript → dist/
COPY tsconfig.json ./
COPY src ./src
RUN npm run build

# Prune dev dependencies — only production deps remain in node_modules
RUN npm prune --omit=dev

# ─── Stage 2: Runtime ─────────────────────────────────────────────────────────
FROM node:20-alpine AS runtime

RUN apk add --no-cache openssh-client

WORKDIR /app

# Copy pruned node_modules and compiled dist from builder
COPY --from=builder /app/node_modules ./node_modules
COPY --from=builder /app/dist ./dist
COPY package.json ./

ENV NODE_ENV=production
ENV PORT=3333

EXPOSE 3333

HEALTHCHECK --interval=30s --timeout=10s --start-period=20s --retries=3 \
  CMD node -e "require('http').get('http://localhost:3333/',r=>{process.exit(r.statusCode<500?0:1)}).on('error',()=>process.exit(1))"

CMD ["node", "dist/server.js"]
