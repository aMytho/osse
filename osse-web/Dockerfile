FROM oven/bun:alpine AS builder

WORKDIR /web

# Cache dependencies
COPY package.json pnpm-lock.yaml ./
RUN bun install

# Copy source
COPY . .

# Build Angular app
RUN bun run build --configuration="production"

CMD ["true"]
