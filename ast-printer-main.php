<?php
spl_autoload_register(function ($class)
{
	$class = strtolower($class);
	$class = str_replace('\\', '/', $class);
	require($class.'.php');
});

require_once('tokentype.php');
require_once('ast-printer.php');

$expression = new BinaryExpr(
	new UnaryExpr(
		new Token(TOK_MINUS, "-", null, 1),
		new LiteralExpr(123)
	),
	new Token(TOK_STAR, "*", null, 1),
	new GroupingExpr(
		new LiteralExpr(45.67)
	)
);
$statement = new ExpressionStmt($expression);

echo (new AstPrinter())->print($statement)."\n";

$optimizer = new Optimizer();
$statements = $optimizer->optimize([$statement]);

echo (new AstPrinter())->print($statements[0])."\n";
