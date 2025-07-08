import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  experimental: {
    serverActions: {
      allowedOrigins: [
        'b24-o8ka2j.bitrix24.ru',
        'possessively-uncommon-dragonet.cloudpub.ru'
      ]
    }
  }
};

export default nextConfig;
