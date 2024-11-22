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
