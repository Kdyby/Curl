<?php

/**
 * Test: Kdyby\Curl\FileResponse.
 *
 * @testCase KdybyTests\Curl\FileResponseTest
 * @author Jaroslav Hranička <hranicka@outlook.com>
 * @package Kdyby\Curl
 */

namespace KdybyTests\Curl;

use Kdyby;
use Tester;

require_once __DIR__ . '/../bootstrap.php';

/**
 * @author Jaroslav Hranička <hranicka@outlook.com>
 */
class FileResponseTest extends Tester\TestCase
{

	public function testPrepareDownload()
	{
		Tester\Assert::same('edd3d40f4990fdd2a0eeea582e7f59e813cf1113', Kdyby\Curl\FileResponse::sanitizeFileName('http://www.example.com/index.php/xml/export/exporter?859663ab&a=ae348c829&b=ea848c620&c=ee348c990&d=85tg8c82&e=9638c887&p=8/3'));

		Tester\Assert::same('38be154045ca4583a9b60b5674911edebd1fe966', Kdyby\Curl\FileResponse::sanitizeFileName('http://www.example.com/9c111nmvuC/xua/BADEDHBI/LKGEIFI/A/A/B?j8yt=q1uq-4rsx4t.sF&A71=x995%JQ%IV%IVCCC.0q8q.sF%IVsx4Bq9u180u-5497urE%IV%IVsF%IVCCC-Fr4Fy-sF.D21<<x995://CCC.zt464sE.s42:OG/s1ys0-NNMKOLO-HGPGJNHO-HK&z=edd3d40f4990fdd2a0eeea582e7f59e813cf1113edd3d40f4990fdd2a0eeea582e7f59e813cf1113edd3d40f4990fdd2a0eeea582e7f59e813cf1113edd3d40f4990fdd2a0eeea582e7f59e813cf1113edd3d40f4990fdd2a0eeea582e7f59e813cf1113'));
	}

}

\run(new FileResponseTest());
