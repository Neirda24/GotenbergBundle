<?php

namespace Sensiolabs\GotenbergBundle\DependencyInjection;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\ParserConfig;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflector\DefaultReflector;
use Roave\BetterReflection\SourceLocator\Type\SingleFileSourceLocator;
use Sensiolabs\GotenbergBundle\Builder\Attributes\ExposeSemantic;
use Sensiolabs\GotenbergBundle\Builder\Attributes\SemanticNode;
use Sensiolabs\GotenbergBundle\Builder\BuilderInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\TypeInfo\TypeResolver\TypeResolver;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TypeAliasTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use ReflectionMethod;

use function array_merge;
use function array_pop;
use function array_shift;
use function dd;
use function explode;
use function implode;

/**
 * @internal
 */
final class BuilderStack
{
    /**
     * @var array<list<class-string<BuilderInterface>>, string>
     */
    private array $builders = [];

    /**
     * @var array<string, class-string<BuilderInterface>>
     */
    private array $typeReverseMapping = [];

    /**
     * @var array<class-string<BuilderInterface>, array<string, string>>
     */
    private array $configMapping = [];

    /**
     * @var array<string, array<class-string<BuilderInterface>, NodeDefinition>>
     */
    private array $configNode = [];

    private TypeResolver $typeResolver;
    private GuessConfigFromMethod $guess;

    public function __construct()
    {
        $this->typeResolver = TypeResolver::create();
        $this->guess = new GuessConfigFromMethod();
    }

    /**
     * @param 'pdf'|'screenshot'             $type
     * @param class-string<BuilderInterface> $class
     */
    public function push(string $type, string $class): void
    {
        if (!is_a($class, BuilderInterface::class, true)) {
            throw new \LogicException('logic');
        }

        if (\array_key_exists($class, $this->builders)) {
            return; // TODO : understand why this is called two times on fresh cache with tests
            throw new \LogicException('logic');
        }

        $this->builders[$class] = $type;

        $reflection = new \ReflectionClass($class);
        $nodeAttributes = $reflection->getAttributes(SemanticNode::class);

        if (\count($nodeAttributes) === 0) {
            throw new \LogicException(\sprintf('%s is missing the %s attribute', $class, SemanticNode::class));
        }

        /** @var SemanticNode $semanticNode */
        $semanticNode = $nodeAttributes[0]->newInstance();

        $this->typeReverseMapping[$semanticNode->name] = $class;

        $treeBuilder = new TreeBuilder($semanticNode->name);
        $root = $treeBuilder->getRootNode()->addDefaultsIfNotSet();

        foreach (array_reverse($reflection->getMethods(\ReflectionMethod::IS_PUBLIC)) as $method) {
            $attributes = $method->getAttributes(ExposeSemantic::class);
            if (\count($attributes) === 0) {
                continue;
            }

            [$nodeName, $node] = $this->guess->convert($method);

            $root->append($node);

            $this->configMapping[$class] ??= [];
            $this->configMapping[$class][$nodeName] = $method->getName();
        }

        $this->configNode[$type] ??= [];
        $this->configNode[$type][$class] = $root;
    }

    /**
     * @return array<list<class-string<BuilderInterface>>, string>
     */
    public function getBuilders(): array
    {
        return $this->builders;
    }

    /**
     * @return array<string, class-string<BuilderInterface>>
     */
    public function getTypeReverseMapping(): array
    {
        return $this->typeReverseMapping;
    }

    /**
     * @return array<class-string<BuilderInterface>, array<string, string>>
     */
    public function getConfigMapping(): array
    {
        return $this->configMapping;
    }

    /**
     * @return array<string, array<class-string<BuilderInterface>, NodeDefinition>>
     */
    public function getConfigNode(): array
    {
        return $this->configNode;
    }
}
