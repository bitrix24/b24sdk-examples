# Old demo

```javascript
const now = new Date();
const sixMonthAgo = new Date();
sixMonthAgo.setMonth(now.getMonth() - 6);

BX24.callMethod(
    'crm.deal.list',
    {
        select: [
            'ID',
            'TITLE',
            'TYPE_ID',
            'CATEGORY_ID',
            'STAGE_ID',
            'OPPORTUNITY',
            'IS_MANUAL_OPPORTUNITY',
            'ASSIGNED_BY_ID',
            'DATE_CREATE',
        ],
        filter: {
            '=%TITLE': '%а',
            CATEGORY_ID: 1,
            TYPE_ID: 'COMPLEX',
            STAGE_ID: 'C1:NEW',
            '>OPPORTUNITY': 10000,
            '<=OPPORTUNITY': 20000,
            IS_MANUAL_OPPORTUNITY: 'Y',
            '@ASSIGNED_BY_ID': [1, 6],
            '>DATE_CREATE': sixMonthAgo,
        },
        order: {
            TITLE: 'ASC',
            OPPORTUNITY: 'ASC',
        },
    },
    (result) => {
        result.error()
            ? console.error(result.error())
            : console.info(result.data())
        ;
    },
);
```

# fetchListMethod

```typescript
import { B24Hook, LoggerBrowser } from '@bitrix24/b24jssdk'
import type { DateTime } from 'luxon'

const $logger = LoggerBrowser.build(
  'Demo: test:fetchListMethod(crm.deal.list.get)',
  import.meta.dev
)

const $b24 = B24Hook.fromWebhookUrl(config.b24Hook)
$b24.setLogger($logger)

/**
 * For security reasons, B24Hook should only work on the server side.
 */
if( typeof window !== 'undefined' ) {
  throw new Error('It is not safe to use hook requests on the client side')
}

interface CrmSomeEntity {
  id: number
  title: string
  createdTime: DateTime
  detailUrl: string
}
  
const now = new Date();
const sixMonthAgo = new Date();
sixMonthAgo.setMonth(now.getMonth() - 6);

let ttl = 0
const items: CrmSomeEntity[] = []
try {
  const generator = $b24.fetchListMethod(
    'crm.deal.list',
    {
      filter: {
        '=%TITLE': 'd%',
        'CATEGORY_ID': 1,
        'TYPE_ID': 'COMPLEX',
        'STAGE_ID': 'C1:NEW',
        '>OPPORTUNITY': 10000,
        '<=OPPORTUNITY': 20000,
        'IS_MANUAL_OPPORTUNITY': 'Y',
        '@ASSIGNED_BY_ID': [1, 6],
        '>DATE_CREATE': sixMonthAgo
      },
      select: [
        'ID',
        'TITLE',
        'TYPE_ID',
        'CATEGORY_ID',
        'STAGE_ID',
        'OPPORTUNITY',
        'IS_MANUAL_OPPORTUNITY',
        'ASSIGNED_BY_ID',
        'DATE_CREATE',
      ],
      order: {
        TITLE: 'ASC',
        OPPORTUNITY: 'ASC',
      }
    },
    'ID'
  )
  
  for await (const entities of generator) {
    for (const item of entities) {
      ttl++
      items.push({
        id: Number(item.ID),
        title: item.TITLE,
        createdTime: item.DATE_CREATE,
        detailUrl: `${$b24.getTargetOrigin()}/crm/deal/details/${item.ID}/`
      })
      $logger.log(`step: ${ttl}`)
    }
  }

  $logger.log(items)
} catch (error: any) {
  $logger.error(error)
}
```

# callListMethod

```typescript
import { B24Hook, LoggerBrowser } from '@bitrix24/b24jssdk'
import type { DateTime } from 'luxon'

const $logger = LoggerBrowser.build(
  'Demo: test:callListMethod(crm.deal.list.get)',
  import.meta.dev
)

const $b24 = B24Hook.fromWebhookUrl(config.b24Hook)
$b24.setLogger($logger)

/**
 * For security reasons, B24Hook should only work on the server side.
 */
if( typeof window !== 'undefined' ) {
  throw new Error('It is not safe to use hook requests on the client side')
}

interface CrmSomeEntity {
  id: number
  title: string
  createdTime: DateTime
  detailUrl: string
}
  
const now = new Date();
const sixMonthAgo = new Date();
sixMonthAgo.setMonth(now.getMonth() - 6);

let ttl = 0
let items: CrmSomeEntity[] = []
try {
  const response = await $b24.callListMethod(
    'crm.deal.list',
    {
      filter: {
        '=%TITLE': 'd%',
        'CATEGORY_ID': 1,
        'TYPE_ID': 'COMPLEX',
        'STAGE_ID': 'C1:NEW',
        '>OPPORTUNITY': 10000,
        '<=OPPORTUNITY': 20000,
        'IS_MANUAL_OPPORTUNITY': 'Y',
        '@ASSIGNED_BY_ID': [1, 6],
        '>DATE_CREATE': sixMonthAgo
      },
      select: [
        'ID',
        'TITLE',
        'TYPE_ID',
        'CATEGORY_ID',
        'STAGE_ID',
        'OPPORTUNITY',
        'IS_MANUAL_OPPORTUNITY',
        'ASSIGNED_BY_ID',
        'DATE_CREATE',
      ],
      order: {
        TITLE: 'ASC',
        OPPORTUNITY: 'ASC',
      }
    },
    (progress: number) => {
      $logger.log(`step: ${progress}`)
    }
  )
  
  items: CrmSomeEntity[] = (response.getData() || []).map((item: any) => ({
    id: Number(item.ID),
    title: item.TITLE,
    createdTime: item.DATE_CREATE,
    detailUrl: `${$b24.getTargetOrigin()}/crm/deal/details/${item.ID}/`
  }))

  $logger.log(items)
} catch (error: any) {
  $logger.error(error)
}
```

# callMethod

```typescript
import { B24Hook, LoggerBrowser } from '@bitrix24/b24jssdk'
import type { DateTime } from 'luxon'

const $logger = LoggerBrowser.build(
  'Demo: test:callMethod(crm.deal.list.get)',
  import.meta.dev
)

const $b24 = B24Hook.fromWebhookUrl(config.b24Hook)
$b24.setLogger($logger)

/**
 * For security reasons, B24Hook should only work on the server side.
 */
if( typeof window !== 'undefined' ) {
  throw new Error('It is not safe to use hook requests on the client side')
}

interface CrmSomeEntity {
  id: number
  title: string
  createdTime: DateTime
  detailUrl: string
}
  
const now = new Date();
const sixMonthAgo = new Date();
sixMonthAgo.setMonth(now.getMonth() - 6);
const start = 0

let ttl = 0
let items: CrmSomeEntity[] = []
try {
  const response = await $b24.callMethod(
    'crm.deal.list',
    {
      filter: {
        '=%TITLE': 'd%',
        'CATEGORY_ID': 1,
        'TYPE_ID': 'COMPLEX',
        'STAGE_ID': 'C1:NEW',
        '>OPPORTUNITY': 10000,
        '<=OPPORTUNITY': 20000,
        'IS_MANUAL_OPPORTUNITY': 'Y',
        '@ASSIGNED_BY_ID': [1, 6],
        '>DATE_CREATE': sixMonthAgo
      },
      select: [
        'ID',
        'TITLE',
        'TYPE_ID',
        'CATEGORY_ID',
        'STAGE_ID',
        'OPPORTUNITY',
        'IS_MANUAL_OPPORTUNITY',
        'ASSIGNED_BY_ID',
        'DATE_CREATE',
      ],
      order: {
        TITLE: 'ASC',
        OPPORTUNITY: 'ASC',
      }
    },
    start
  )
  
  items: CrmSomeEntity[] = (response.getData().result || []).map((item: any) => ({
    id: Number(item.ID),
    title: item.TITLE,
    createdTime: item.DATE_CREATE,
    detailUrl: `${$b24.getTargetOrigin()}/crm/deal/details/${item.ID}/`
  }))

  $logger.log(items)
} catch (error: any) {
  $logger.error(error)
}
```

