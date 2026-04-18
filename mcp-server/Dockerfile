FROM node:20-alpine

# Build tools needed for ssh2 native bindings (cpu-features)
RUN apk add --no-cache python3 make g++ openssh-client

WORKDIR /app

# Install all dependencies (including dev for TypeScript build)
COPY package*.json ./
RUN npm ci

# Build TypeScript → dist/
COPY tsconfig.json ./
COPY src ./src
RUN npm run build

# Remove dev dependencies after build
RUN npm prune --omit=dev

ENV NODE_ENV=production
ENV PORT=3333

EXPOSE 3333

HEALTHCHECK --interval=30s --timeout=10s --start-period=20s --retries=3 \
  CMD node -e "require('http').get('http://localhost:3333/',r=>{process.exit(r.statusCode<500?0:1)}).on('error',()=>process.exit(1))"

CMD ["node", "dist/server.js"]
