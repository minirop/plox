<?php
require_once('scanner.php');
require_once('parser.php');
require_once('interpreter.php');

if ($argc > 2)
{
	print("Usage: plox [script]\n");
}
else if ($argc == 2)
{
	runFile($argv[1]);
}
else
{
	runPrompt();
}

function runFile($filename)
{
	$str = file_get_contents($filename);
	run($str);
}

function runPrompt()
{
	while (true)
	{
		print ('> ');
		$str = trim(fgets(STDIN));
		run($str);
	}
}

function run($source)
{
	$scanner = new Scanner($source);
	$tokens = $scanner->scanTokens();

	$parser = new Parser($tokens);
	$statements = $parser->parse();

	if (EPlox::$hadError) return;

	(new Interpreter())->interpret($statements);
}
