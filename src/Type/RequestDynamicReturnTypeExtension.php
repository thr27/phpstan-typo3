<?php declare(strict_types = 1);

namespace SaschaEgerer\PhpstanTypo3\Type;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDoc\TypeStringResolver;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Psr\Http\Message\ServerRequestInterface;

class RequestDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	/** @var array<string, string> */
	private $requestGetAttributeMapping;

	/** @var TypeStringResolver */
	private $typeStringResolver;

	/**
	 * @param array<string, string> $requestGetAttributeMapping
	 */
	public function __construct(array $requestGetAttributeMapping, TypeStringResolver $typeStringResolver)
	{
		$this->requestGetAttributeMapping = $requestGetAttributeMapping;
		$this->typeStringResolver = $typeStringResolver;
	}

	public function getClass(): string
	{
		if (!interface_exists(ServerRequestInterface::class)) {
			throw new \PHPStan\ShouldNotHappenException(
				'The package "psr/http-message" is not installed, but should be.'
			);
		}

		return ServerRequestInterface::class;
	}

	public function getTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope
	): Type
	{
		$argument = $methodCall->getArgs()[0] ?? null;
		$defaultArgument = $methodCall->getArgs()[1] ?? null;

		if ($argument === null
			|| !($argument->value instanceof \PhpParser\Node\Scalar\String_)
			|| !isset($this->requestGetAttributeMapping[$argument->value->value])
		) {
			$type = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();

			if ($defaultArgument === null) {
				return $type;
			}

			return TypeCombinator::union($type, $scope->getType($defaultArgument->value));
		}

		$type = $this->typeStringResolver->resolve($this->requestGetAttributeMapping[$argument->value->value]);

		if ($defaultArgument === null) {
			return TypeCombinator::addNull($type);
		}

		return TypeCombinator::union($type, $scope->getType($defaultArgument->value));
	}

	public function isMethodSupported(
		MethodReflection $methodReflection
	): bool
	{
		return $methodReflection->getName() === 'getAttribute';
	}

}
