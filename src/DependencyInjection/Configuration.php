<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\DependencyInjection;

use DateTimeImmutable;
use SheGroup\VerifactuBundle\Model\Certificate;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Throwable;

final class Configuration implements ConfigurationInterface
{
    public const NAME = 'she_group_verifactu';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(self::NAME);
        $nodeBuilder = $rootNode->children();
        $this->addComputerSystemConfig($nodeBuilder);
        $this->addCertificateConfig($nodeBuilder);

        return $treeBuilder;
    }

    private function addComputerSystemConfig(NodeBuilder $nodeBuilder): void
    {
        $computerSystem = $nodeBuilder->arrayNode('computer_system')->isRequired()->cannotBeEmpty()->children();
        $computerSystem->booleanNode('production')->isRequired()->defaultFalse()->end();
        $computerSystem->scalarNode('id')->isRequired()->cannotBeEmpty()->validate()
            ->ifTrue(fn ($value) => $this->exceedsLength($value, 2))
            ->thenInvalid('Invalid Computer System ID. Expected 2 characters at most.')
            ->end();
        $computerSystem->scalarNode('name')->isRequired()->cannotBeEmpty()->validate()
            ->ifTrue(fn ($value) => $this->exceedsLength($value, 30))
            ->thenInvalid('Invalid Computer System name. Expected 30 characters at most.')
            ->end();
        $computerSystem->scalarNode('vendor_id')->isRequired()->cannotBeEmpty()->validate()
            ->ifTrue(fn ($value) => $this->hasNotExactLength($value, 9))
            ->thenInvalid('Invalid Computer System vendor ID. Expected exactly 9 characters.')
            ->end();
        $computerSystem->scalarNode('vendor_name')->isRequired()->cannotBeEmpty()->validate()
            ->ifTrue(fn ($value) => $this->exceedsLength($value, 120))
            ->thenInvalid('Invalid Computer System vendor name. Expected 120 characters at most.')
            ->end();
        $computerSystem->scalarNode('version')->isRequired()->cannotBeEmpty()->validate()
            ->ifTrue(fn ($value) => $this->exceedsLength($value, 50))
            ->thenInvalid('Invalid Computer System version. Expected 50 characters at most.')
            ->end();
        $computerSystem->scalarNode('installation_number')->isRequired()->cannotBeEmpty()->validate()
            ->ifTrue(fn ($value) => $this->exceedsLength($value, 100))
            ->thenInvalid('Invalid Computer System installation number. Expected 100 characters at most.')
            ->end();
        $computerSystem->booleanNode('only_supports_verifactu')->defaultTrue()->end();
        $computerSystem->booleanNode('supports_multiple_taxpayers')->defaultFalse()->end();
        $computerSystem->booleanNode('has_multiple_taxpayers')->defaultFalse()->end();
    }

    private function addCertificateConfig(NodeBuilder $nodeBuilder): void
    {
        $certificate = $nodeBuilder->arrayNode('certificate')->isRequired()->cannotBeEmpty()->children();
        $certificate->booleanNode('enabled')->isRequired()->defaultFalse()->end();
        $certificate->scalarNode('expiration_warning')->isRequired()->cannotBeEmpty()->defaultValue('30 days')
            ->validate()
            ->ifTrue(static function (string $value): bool {
                try {
                    new DateTimeImmutable(sprintf('now + %s', $value));

                    return false;
                } catch (Throwable $e) {
                    return true;
                }
            })
            ->thenInvalid(
                sprintf(
                    '%s. %s.',
                    'Invalid expiration warning value',
                    'It should be a valid relative date (30 days, 1 month, 1 year, etc.)'
                )
            );
        $certificate->scalarNode('path')->isRequired()->cannotBeEmpty()->end();
        $certificate->scalarNode('password')->isRequired()->cannotBeEmpty()->end();
        $certificate = $certificate->end();
        $certificate->validate()
            ->ifTrue(static function (array $value): bool {
                if (!$value['enabled']) {
                    return false;
                }

                return !file_exists($value['path']);
            })
            ->thenInvalid('Certificate file does not exists.')
            ->end();
        $certificate->validate()
            ->ifTrue(static function (array $value): bool {
                if (!$value['enabled']) {
                    return false;
                }

                return !Certificate::isValidCertificate($value['path'], $value['password']);
            })
            ->thenInvalid('Invalid certificate or password.')
            ->end();
    }

    private function exceedsLength(string $value, int $length): bool
    {
        return mb_strlen($value) > $length;
    }

    /* @noinspection PhpSameParameterValueInspection */
    private function hasNotExactLength(string $value, int $length): bool
    {
        return mb_strlen($value) !== $length;
    }
}
