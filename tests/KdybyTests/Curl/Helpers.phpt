<?php

/**
 * Test: Kdyby\Curl\Helpers::flattenArray.
 *
 * @testCase KdybyTests\Curl\Helpers
 * @author Jan Endel <jan@endel.cz>
 * @package Kdyby\Curl
 */

namespace KdybyTests\Curl;

use Kdyby\Curl\Helpers;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Jan Endel <jan@endel.cz>
 */
class RequestTest extends Tester\TestCase
{

	public function testFlattenArray()
	{
		$toFlatten = array(
			"first" => "First level",
			array(
				"second" => "Second level",
				array(
					"third" => "Third level",
					"third2" => "Third two",
				),
				array(
					"third" => "Third level",
					"third2" => "Third two",
				),
			),
		);

		$afterFlatten = array(
			'first' => "First level",
			'0[second]' => "Second level",
			'0[0][third]' => "Third level",
			'0[0][third2]' => "Third two",
			'0[1][third]' => "Third level",
			'0[1][third2]' => "Third two",
		);

		$toFlatten = Helpers::flattenArray($toFlatten);
		Assert::same($afterFlatten, $toFlatten);
	}
}



\run(new RequestTest());
