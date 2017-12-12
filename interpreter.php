<?php
class RuntimeError extends Exception
{
	public $token;

	public function __construct(Token $token, $message)
	{
		parent::__construct($message);
		$this->token = $token;
	}
}

class Interpreter implements VisitorExpr, VisitorStmt
{
	public $globals;
	private $environment;
	private $locals = [];

	public function __construct()
	{
		$this->globals = new Environment();
		$this->environment = $this->globals;

		$this->globals->define("clock", new \Std\Clock());
	}

	public function interpret(Array $statements)
	{
		try
		{
			foreach ($statements as $statement)
			{
				$this->execute($statement);
			}
		}
		catch (RuntimeError $error)
		{
			EPLox::runtimeError($error);
		}
	}

	private function hasLocal(Expr $expr)
	{
		foreach ($this->locals as $arr)
		{
			if ($arr[0] === $expr)
			{
				return true;
			}
		}
		return false;
	}

	private function getLocal(Expr $expr)
	{
		foreach ($this->locals as $arr)
		{
			if ($arr[0] === $expr)
			{
				return $arr[1];
			}
		}
		return null;
	}

	private function setLocal(Expr $expr, $value)
	{
		foreach ($this->locals as &$arr)
		{
			if ($arr[0] === $expr)
			{
				$arr[1] = $value;
			}
		}
		
		$this->locals[] = [$expr, $value];
	}

	public function visitAssignExpr(AssignExpr $expr)
	{
		$value = $this->evaluate($expr->value);

		if ($this->hasLocal($expr))
		{
			$distance = $this->getLocal($expr);
			$this->environment->assignAt($distance, $name->literal, $value);
		}
		else
		{
			$this->globals[$name] = $value;
		}
		
		return $value;
	}

	public function visitBinaryExpr(BinaryExpr $expr)
	{
		$left = $this->evaluate($expr->left);
		$right = $this->evaluate($expr->right);

		switch ($expr->operator->type)
		{
			case TOK_PLUS:
				if (is_double($left) && is_double($right))
				{
					return doubleval($left) + doubleval($right);
				}

				if (is_string($left) && is_string($right))
				{
					return $left.''.$right;
				}
				return null;
			case TOK_MINUS:
				$this->checkNumberOperand($expr->operator, $right);
				return doubleval($left) - doubleval($right);
			case TOK_SLASH:
				return doubleval($left) / doubleval($right);
			case TOK_STAR:
				return doubleval($left) * doubleval($right);

			case TOK_GREATER:
				return (double)$left > (double)$right;
			case TOK_GREATER_EQUAL:
				return (double)$left >= (double)$right;
			case TOK_LESS:
				return (double)$left < (double)$right;
			case TOK_LESS_EQUAL:
				return (double)$left <= (double)$right;
			case TOK_BANG_EQUAL: return !$this->isEqual($left, $right);
			case TOK_EQUAL_EQUAL: return $this->isEqual($left, $right);
		}

		return null;
	}

	public function visitCallExpr(CallExpr $expr)
	{
		$callee = $this->evaluate($expr->callee);

		$arguments = [];
		foreach ($expr->arguments as $argument)
		{
			$arguments[] = $this->evaluate($argument);
		}

		if (!($callee instanceof LoxCallable))
		{
			throw new RuntimeError($expr->paren, "Can only call functions and classes.");
		}

		if (count($arguments) != $callee->arity())
		{
			throw new RuntimeError($expr->paren,
				"Expected ".$callee->arity()." arguments but got ".count($arguments).".");
		}

		return $callee->call($this, $arguments);
	}
	
	public function visitGetExpr(GetExpr $expr)
	{
		$object = $this->evaluate($expr->object);
		if ($object instanceof LoxInstance)
		{
			return $object->get($expr->name);
		}

		throw new RuntimeError($expr->name, "Only instances have properties.");
	}
	
	public function visitGroupingExpr(GroupingExpr $expr)
	{
		return $this->evaluate($expr->expression);
	}
	
	public function visitLiteralExpr(LiteralExpr $expr)
	{
		return $expr->value;
	}
	
	public function visitLogicalExpr(LogicalExpr $expr)
	{
		$left = $this->evaluate($expr->left);

		if ($expr->operator->type == TOK_OR)
		{
			if ($this->isTruthy($left)) return $left;
		}
		else
		{
			if (!$this->isTruthy($left)) return $left;
		}

		return $this->evaluate($expr->right);
	}
	
	public function visitSetExpr(SetExpr $expr)
	{
		$object = $this->evaluate($expr->object);

		if (!($object instanceof LoxInstance)) { 
			throw new RuntimeError($expr->name, "Only instances have fields.");
		}

		$value = $this->evaluate($expr->value);
		$object->set($expr->name, $value);
		return $value;
	}
	
	public function visitSuperExpr(SuperExpr $expr)
	{
		$distance = $this->getLocal($expr);
		$superclass = $this->environment->getAt($distance, "super");
		$object = $this->environment->getAt($distance - 1, "this");

		$method = $superclass->findMethod($object, $expr->method->literal);

		if ($method == null)
		{
			throw new RuntimeError($expr->method, "Undefined property '" . $expr->method->literal . "'.");
		}

		return $method;
	}
	
	public function visitThisExpr(ThisExpr $expr)
	{
		return $this->lookUpVariable($expr->keyword, $expr);
	}
	
	public function visitUnaryExpr(UnaryExpr $expr)
	{
		$right = $this->evaluate($expr->right);

		switch ($right->operator->type)
		{
			case TOK_MINUS:
				return - doubleval($right);
			case TOK_BANG:
				return !$this->isTruthy($right);
		}

		return null;
	}
	
	public function visitVariableExpr(VariableExpr $expr)
	{
		return $this->lookUpVariable($expr->name, $expr);
	}

	public function visitBlockStmt(BlockStmt $stmt)
	{
		$this->executeBlock($stmt->statements, new Environment($this->environment));
	}

	public function visitClassStmt(ClassStmt $stmt)
	{
		$this->environment->define($stmt->name->literal, null);

		$superclass = null;
		if ($stmt->superclass != null)
		{
			$superclass = $this->evaluate($stmt->superclass);
			if (!($superclass instanceof LoxClass))
			{
				throw new RuntimeError($stmt->name, "Superclass must be a class.");
			}

			$this->environment = new Environment($this->environment);
			$this->environment->define("super", $superclass);
		}

		$methods = [];
		foreach ($stmt->methods as $method)
		{
			$function = new LoxFunction($method, $this->environment, ($method->name->literal === "init"));
			$methods[$method->name->literal] = $function;
		}

		$klass = new LoxClass($stmt->name->literal, $superclass, $methods);

		if ($superclass != null)
		{
			$this->environment = $this->environment->getEnclosing();
		}

		$this->environment->assign($stmt->name, $klass);
	}

	public function visitExpressionStmt(ExpressionStmt $stmt)
	{
		$this->evaluate($stmt->expression);
	}

	public function visitFunctionStmt(FunctionStmt $stmt)
	{
		$function = new LoxFunction($stmt, $this->environment, false);
		$this->environment->define($stmt->name->literal, $function);
	}

	public function visitIfStmt(IfStmt $stmt)
	{
		if ($this->isTruthy($this->evaluate($stmt->condition)))
		{
			$this->execute($stmt->thenBranch);
		}
		else if ($stmt->elseBranch !== null)
		{
			$this->execute($stmt->elseBranch);
		}
	}

	public function visitPrintStmt(PrintStmt $stmt)
	{
		$value = $this->evaluate($stmt->expression);
		print($this->stringify($value)."\n");
	}

	public function visitReturnStmt(ReturnStmt $stmt)
	{
		$value = null;
		if ($stmt->value !== null)
			$value = $this->evaluate($stmt->value);

		throw new ReturnException($value);
	}

	public function visitVarStmt(VarStmt $stmt)
	{
		$value = null;
		if ($stmt->initializer !== null)
		{
			$value = $this->evaluate($stmt->initializer);
		}

		$this->environment->define($stmt->name->literal, $value);
	}

	public function visitWhileStmt(WhileStmt $stmt)
	{
		while ($this->isTruthy($this->evaluate($stmt->condition)))
		{
			$this->execute($stmt->body);
		}
	}

	private function stringify($object)
	{
		if ($object === null) return "nil";

		return (string)$object;
	}

	private function evaluate(Expr $expr)
	{
		return $expr->accept($this);
	}

	private function isTruthy($object)
	{
		if ($object === null) return false;
		if (is_bool($object)) return boolval($object);

		return true;
	}

	private function isEqual($a, $b)
	{
		if ($a === null) return ($b === null);
		return $a == $b;
	}

	private function checkNumberOperand(Token $operator, $operand)
	{
		if (is_double($operand)) return;
		throw new RuntimeError("Operand must be a number");
	}

	private function lookUpVariable(Token $name, Expr $expr)
	{
		if ($this->hasLocal($expr))
		{
			$distance = $this->getLocal($expr);
			return $this->environment->getAt($distance, $name->literal);
		}
		else
		{
			return $this->globals->get($name);
		}
	}

	private function execute(Stmt $stmt)
	{
		$stmt->accept($this);
	}

	public function executeBlock(Array $statements, Environment $environment)
	{
		$previous = $this->environment;
		try
		{
			$this->environment = $environment;

			foreach ($statements as $statement)
			{
				$this->execute($statement);
			}
		}
		finally
		{
			$this->environment = $previous;
		}
	}

	public function resolve(Expr $expr, $depth)
	{
		$this->setLocal($expr, $depth);
	}
}
