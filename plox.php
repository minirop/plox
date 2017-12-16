<?php
spl_autoload_register(function ($class)
{
	$class = strtolower($class);
	$class = str_replace('\\', '/', $class);
	require($class.'.php');
});

require_once('tokentype.php');
require_once('ast.php');

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
	$interpreter = new Interpreter();
	
	$scanner = new Scanner($source);
	$tokens = $scanner->scanTokens();

	$parser = new Parser($tokens);
	$statements = $parser->parse();

	if (EPlox::$hadError) return;

	$resolver = new Resolver($interpreter);
	$resolver->resolve($statements);
	unset($resolver);

	if (EPlox::$hadError) return;

	$interpreter->interpret($statements);
}
