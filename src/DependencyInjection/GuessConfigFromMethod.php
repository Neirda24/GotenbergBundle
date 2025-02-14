<?php

namespace Sensiolabs\GotenbergBundle\DependencyInjection;

use PhpParser\Node\UnionType;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TypeAliasTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\ParserConfig;
use ReflectionMethod;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflector\DefaultReflector;
use Roave\BetterReflection\SourceLocator\Type\SingleFileSourceLocator;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use UnitEnum;
use BackedEnum;
use function array_merge;
use function array_pop;
use function array_shift;
use function explode;
use function implode;
use function ltrim;
use function trim;

/**
 * @internal
 */
final class GuessConfigFromMethod
{
    /**
     * @return array{string, NodeDefinition}
     */
    public function convert(ReflectionMethod $method): array
    {
        // 1️⃣ Initialisation des parseurs PHPStan
        $parserConfig = new ParserConfig([]);
        $lexer = new Lexer($parserConfig);
        $constExprParser = new ConstExprParser($parserConfig);
        $typeParser = new TypeParser($parserConfig, $constExprParser);
        $phpDocParser = new PhpDocParser($parserConfig, $typeParser, $constExprParser);

// 2️⃣ Récupérer le PHPDoc de la méthode
        $phpDocBlock = $method->getDocComment();

        $infoText = null;
        $examples = [];

// 3️⃣ Extraire `info()` et `example()`
        if ($phpDocBlock) {
            $tokens = new TokenIterator($lexer->tokenize($phpDocBlock));
            $phpDocNode = $phpDocParser->parse($tokens);

//            $infoText = trim(preg_replace('/@\w+.*$/m', '', $phpDocBlock)) ?: null; // Supprimer les tags
            $infoText = $this->parseDocComment($phpDocBlock); // Supprimer les tags
            preg_match_all('/@example\s+(.+)/', $phpDocBlock, $matches);
            $examples = $matches[1] ?? [];
        }

// 4️⃣ Fonction récursive pour construire le TreeBuilder

// 5️⃣ Construire le TreeBuilder
        $snakeCase = preg_replace_callback(
            '/[A-Z]/',
            function($matches) {
                return '_' . strtolower($matches[0]);
            },
            $method->getName()
        );

        $nodeName = ltrim($snakeCase, '_');

        $treeBuilder = new TreeBuilder($nodeName);
        $rootNode = $treeBuilder->getRootNode();

        if ($infoText !== null) {
            $rootNode->info($infoText);
        }

        if (!empty($examples)) {
            $rootNode->example(count($examples) === 1 ? $examples[0] : $examples);
        }

        $astLocator = (new BetterReflection())->astLocator();
        $reflector = new DefaultReflector(new SingleFileSourceLocator($method->getFileName(), $astLocator));
        $originalClass = $reflector->reflectAllClasses()[0];

        $originalClassDocBlock = $originalClass->getDocComment() ?? '';
        $tmp = explode("\n", $originalClassDocBlock);
        array_shift($tmp);
        array_pop($tmp);
        $originalClassDocBlock = implode("\n", $tmp);

        $tmp = explode("\n", $phpDocBlock);
        array_shift($tmp);
        array_pop($tmp);
        $phpDocBlock = implode("\n", $tmp);

        $globalDocBlock = "/**\n".trim($originalClassDocBlock."\n".$phpDocBlock, "\n")."\n*/";

        $tokens = new TokenIterator(array_merge(
            $lexer->tokenize($globalDocBlock),
        ));
        $phpDocNode = $phpDocParser->parse($tokens);

        $typeAliases = [];
        $paramTypes = [];

// 4️⃣ Parcourir les annotations et stocker les alias
        foreach ($phpDocNode->children as $child) {
            if ($child instanceof PhpDocTagNode && $child->value instanceof TypeAliasTagValueNode) {
                $typeAliases[$child->value->alias] = (string) $child->value->type;
            }
        }

// 5️⃣ Résoudre les paramètres en remplaçant les alias locaux
        foreach ($phpDocNode->children as $child) {
            if ($child instanceof PhpDocTagNode && $child->value instanceof ParamTagValueNode) {
                $type = (string) $child->value->type;

                // Si le type est un alias local, on le remplace par sa définition
                if (isset($typeAliases[$type])) {
                    $type = $typeAliases[$type];
                }

                $paramTypes[$child->value->parameterName] = $type;
            }
        }

// 6️⃣ Affichage des types résolus
        foreach ($paramTypes as $paramName => $paramType) {
            $parsedType = $typeParser->parse(new TokenIterator($lexer->tokenize($paramType)));

            $this->convertTypeToTree($rootNode, $parsedType);
        }

// 6️⃣ Affichage du résultat
        return [$nodeName, $rootNode];
    }

    private function convertTypeToTree(ArrayNodeDefinition $node, TypeNode $type, string|null $name = null): void {
        dump($type, $name, '==============');
        if ($type instanceof ArrayShapeNode) {
            // Gérer les array shape récursivement
            foreach ($type->items as $item) {
                $key = $item->keyName;
                $valueType = $item->valueType;
                $this->convertTypeToTree($node, $valueType, $key);
            }
        } elseif ($type instanceof IdentifierTypeNode) {
            // Types scalaires
            switch ($type->name) {
//                case 'int':
//                    $node->children()->integerNode($name)->isRequired()->cannotBeEmpty();
//                    break;
//                case 'string':
//                    $node->children()->scalarNode($name)->isRequired()->cannotBeEmpty();
//                    break;
//                case 'bool':
//                    $node->children()->booleanNode($name)->isRequired();
//                    break;
//                case 'float':
//                    $node->children()->floatNode($name)->isRequired()->cannotBeEmpty();
//                    break;
//                case 'array':
//                    $node->requiresAtLeastOneElement();
//                    break;
                case 'list':
                    /** @var $type GenericTypeNode */
                    $genericType = $type->genericTypes[0];
                    // TODO : if array then prototype array
                    break;
//                case 'object':
//                    $node->children()->scalarNode($name)->info('Instance d’un objet');
//                    break;
                default:
                    $node->children()->variableNode($name);
            }
        } elseif ($type instanceof UnionTypeNode) {
            // Gérer les union types (ex: `int|string`)
            $allowedTypes = [];
            foreach ($type->types as $subType) {
                if ($subType instanceof IdentifierTypeNode) {
                    $allowedTypes[] = $subType->name;
                }
            }
            if (!empty($allowedTypes)) {
                $node->children()->enumNode('enum')->values($allowedTypes);
            }
        } elseif ($type instanceof GenericTypeNode) {
            // Gérer `array<Foo>` ou `iterable<int, string>`
//            $node->children()->arrayNode('value')->requiresAtLeastOneElement();
            $node->children()->arrayNode('generic');
        } else {
            // Type inconnu
            $node->children()->variableNode('else');
        }
    }

    private function parseDocComment(string $rawDocComment): string
    {
        $result = '';

        $lines = explode("\n", trim($rawDocComment, "\n"));
        array_shift($lines);
        array_pop($lines);

        foreach ($lines as $line) {
            $line = trim($line);
            $line = ltrim($line, '*');

            if (str_starts_with($line, '  ')) {
                continue;
            }

            $line = ltrim($line);

            if ('' === $line) {
                continue;
            }

            if ('}' === $line) {
                continue;
            }

            if (str_starts_with($line, '@')) {
                continue;
            }

            $result .= $line."\n";
        }

        return $result;
    }
}
