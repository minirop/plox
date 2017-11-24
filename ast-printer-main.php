<?php
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

echo (new AstPrinter())->print($expression)."\n";
