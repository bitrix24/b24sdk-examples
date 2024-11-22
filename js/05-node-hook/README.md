# 05-node-hook

The example demonstrates how to use Node.js, TypeScript, and `@bitrix24/b24jssdk` to interact with Bitrix24 using a hook.

## Project Structure

```
my-node-project/
│
├── dist/ // Compiled JavaScript files
├── src/ // Source TypeScript files
│ └── process-company-list.ts // Process of exporting companies from B24
├── out/ // Export results (*.csv)
├── .env.local // Environment variables
├── package.json // Project configuration file
└── tsconfig.json // TypeScript configuration
```

## Steps to Create the Project

1. **Create the project folder:**

```bash
mkdir my-node-project
cd my-node-project
```

2. **Initialize the project:**

```bash
npm init -y
```

3.1 **Install dependencies:**

```bash
npm install typescript @types/node --save-dev
npm install @bitrix24/b24jssdk@latest dotenv chalk --save
```

3.2 **Check `package.json`:**

```json
{
    "name": "my-node-project",
    "type": "module",
    "devDependencies": {
        "@types/node": "^22.9.1",
        "typescript": "^5.6.3"
    },
    "dependencies": {
        "@bitrix24/b24jssdk": "latest",
        "chalk": "^5.3.0",
        "dotenv": "^16.4.5"
    }
}
```

4. **Create `tsconfig.json` for TypeScript configuration:**

```json
{
    "compilerOptions": {
        "target": "ESNext",
        "module": "ESNext",
        "moduleResolution": "node",
        "outDir": "./dist",
        "rootDir": "./src",
        "strict": true,
        "esModuleInterop": true,
        "skipLibCheck": true
    },
    "include": [
        "src"
    ]
}
```

5. **Create the `.env.local` file:**

```text
#################
# Bitrix24 HOOK #
#################
## Specify the domain of Bitrix24. For example: https://your_domain.bitrix24.com
VITE_B24_HOOK_URL="insert_url"

## Enter user ID. For example: 123
VITE_B24_HOOK_USER_ID="insert_user_id"

## Specify the secret. For example: k32t88gf3azpmwv3
VITE_B24_HOOK_SECRET="insert_secret"
```

6. **Create the `process-company-list.ts` file in the `src` folder:**

```ts
// src/process-company-list.ts
import * as dotenv from 'dotenv'
import { fileURLToPath } from 'url'
import { dirname, resolve } from 'path'
import * as fs from 'fs'
import chalk from 'chalk'
import { DateTime, Interval } from 'luxon'
import {
	LoggerBrowser,
	Result,
	B24Hook,
	EnumCrmEntityTypeId,
	Text,
	useFormatter
} from '@bitrix24/b24jssdk'

const __filename = fileURLToPath(import.meta.url)
const __dirname = dirname(__filename)

const $logger = LoggerBrowser.build('process-company-list ', true)
const { formatterNumber } = useFormatter()
formatterNumber.setDefLocale('en')

// Load environment variables from .env.local ////
dotenv.config({ path: resolve(__dirname, '../.env.local') })

// Retrieve the API URL from environment variables and create a B24Hook ////
const $b24 = new B24Hook({
	b24Url: process.env.VITE_B24_HOOK_URL || '',
	userId: Text.toInteger(process.env.VITE_B24_HOOK_USER_ID),
	secret: process.env.VITE_B24_HOOK_SECRET || ''
})
$b24.setLogger(LoggerBrowser.build('Core', false))

// Let's describe the work status interface ////
interface IStatus {
	filePath: string,
	resultInfo: null|string,
	progress: {
		ttl: number,
		lastId: number
	},
	time: {
		start: null|DateTime,
		stop: null|DateTime,
		interval: null|Interval
	}
}

// Generate a filename with a time-based UUID ////
const fileName = `companies-list-${Text.getUuidRfc4122()}.csv`

// Check and create the /out directory if it does not exist ////
const outputDir = resolve(__dirname, '../out')
if(!fs.existsSync(outputDir))
{
	fs.mkdirSync(outputDir, { recursive: true })
}

// Create a write stream to the file ////
const filePath = resolve(outputDir, fileName)
const writeStream = fs.createWriteStream(filePath)

const delim = '%|%'

const status: IStatus = {
	filePath: filePath,
	resultInfo: null,
	progress: {
		ttl: 0,
		lastId: 0
	},
	time: {
		start: null,
		stop: null,
		interval: null,
	}
}

const result = new Result()
result.setData(status)

$logger.info(chalk.green(
	'>> start >>>'
))

try
{
	status.time.start = DateTime.now()
	
	// Write headers to CSV ////
	writeStream.write(['Id', 'Title', 'Industry'].join(delim) + '\n')
	
	let generator = $b24.fetchListMethod(
		'crm.item.list',
		{
			entityTypeId: EnumCrmEntityTypeId.company,
			select: [
				'id',
				'title',
				'industry'
			]
		},
		'id',
		'items'
	)
	
	for await (let entities of generator)
	{
		for(let entity of entities)
		{
			status.progress.ttl++
			status.progress.lastId = Text.toNumber(entity.id)
			
			writeStream.write([
				entity.id,
				entity.title,
				entity.industry
			].join(delim) + '\n')
			process.stdout.write(`\r${ chalk.grey(`>> ttl ${ status.progress.ttl } >>> lastId: ${ entity.id }`) }`)
		}
	}
	
	status.resultInfo = 'all done'
	process.stdout.write('\n')
}
catch(error)
{
	$logger.error(error)
	result.addError(
		(error instanceof Error)
			? error
			: new Error(error as string)
	)
}
finally
{
	// Measure execution time ////
	status.time.stop = DateTime.now()
	if( status.time.stop && status.time.start )
	{
		status.time.interval = Interval.fromDateTimes(status.time.start, status.time.stop)
	}
	
	// Close the stream after writing ////
	writeStream.end()
}

if(result.isSuccess)
{
	const data: IStatus = result.getData()
	$logger.info([
		``,
		`- file: ${chalk.green(data.filePath)}`,
		`- resultInfo: ${chalk.green(data.resultInfo)}`,
		`- ttl: ${chalk.blue(data.progress.ttl)}`,
		`- lastId: ${chalk.gray(data.progress.lastId)}`,
	].join('\n'))
}
else
{
	$logger.error(chalk.red(result.toString()))
}

$logger.info(chalk.green(
	`>> stop >>> ${ formatterNumber.format((status.time?.interval?.length() || 0) / 1_000) } sec`
))
```

7. **Add scripts to `package.json` for compiling and running the project:**

```json
"scripts": {
    "build": "tsc",
    "process-company-list": "npm run build && node dist/process-company-list.js"
}
```

8. **Run the project:**

```bash
npm run process-company-list
```

9. **Result:**

The file `out/companies-list-01935292-7b88-7269-be02-48c99f28c536.csv` will contain the list of imported companies.