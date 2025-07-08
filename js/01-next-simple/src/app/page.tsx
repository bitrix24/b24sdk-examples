'use client'

import { useEffect } from 'react'
import { initializeB24Frame, LoggerBrowser } from '@bitrix24/b24jssdk'

async function init() {
  try {
    console.log('Initializing Bitrix24 SDK...')
    const $logger = LoggerBrowser.build("local-app", true)

    const $b24 = await initializeB24Frame()
    console.log('Bitrix24 SDK initialized:', $b24)
    $b24.setLogger(LoggerBrowser.build('Core'))

    $logger.warn('B24Frame.init');
    const response = await $b24.callMethod('server.time', {})

    $logger.info(`server.time >>`, response.getData());

    return
  } catch (error) {
    console.error('Error in Bitrix24 operation:', error)
    throw error
  }
}

export default function Home() {
  useEffect(() => {
    init()
  }, [])

  return (
    <div className="App">
      <div className="App-header">
        <h1 className="text-2xl font-bold text-center text-gray-800">Bitrix24 Integration</h1>
        <p className="mt-3 text-gray-700-700 font-medium">This Next.js application demonstrates integration with Bitrix24 JS SDK.</p>
      </div>
    </div>
  );
}
