interface B24FormConfig {
  formId: number,
  secret: string,
  loaderScript: string
}

const config = useRuntimeConfig()

const b24FormConfig: B24FormConfig = {
  formId: Number(config.public.b24FormId) || 0,
  secret: config.public.b24FormSecret || '',
  loaderScript: config.public.b24FormLoaderScript || '',
};

export default b24FormConfig;
