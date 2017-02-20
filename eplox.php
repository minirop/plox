<?php
class EPlox
{
	public static $hadError = false;

	public static function error($line, $message)
	{
		self::report($line, '', $message);
	}

	public static function report($line, $where, $message)
	{
		print("[line $line] Error$where: $message\n");
		self::$hadError = true;
	}
}
