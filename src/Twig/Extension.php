<?php
declare(strict_types=1);

namespace Bangpound\Assh\Twig;

use JmesPath\AstRuntime;
use JmesPath\CompilerRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Class AppExtension
 *
 * @package App\Twig
 */
class Extension extends AbstractExtension
{
    /**
     * @var CompilerRuntime
     */
    private $jmesPath;

    /**
     * AppExtension constructor.
     *
     * @param CompilerRuntime $jmesPath
     */
    public function __construct(CompilerRuntime $jmesPath = null)
    {
        $this->jmesPath = $jmesPath ?? new AstRuntime();
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('array_filter', [$this, 'arrayFilter'], ['is_safe' => ['html']]),
            new TwigFilter('property_sort', [$this, 'propertySort'], ['is_safe' => ['html']]),
            new TwigFilter('jp', [$this, 'jp'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('jp', [$this, 'jp']),
            new TwigFunction('str_pad', 'str_pad'),
        ];
    }

    /**
     * @param $input
     *
     * @return array
     */
    public function arrayFilter($input): array
    {
        return array_values(array_filter($input));
    }

    /**
     * @param \Traversable|array $input
     * @param string $property
     *
     * @return array
     * @throws \Twig_Error_Runtime
     */
    public function propertySort($input, string $property): array
    {
        if ($input instanceof \Traversable) {
            $input = iterator_to_array($input);
        } elseif (!\is_array($input)) {
            throw new \Twig_Error_Runtime(sprintf(
                'The sort filter only works with arrays or "Traversable", got "%s".',
                gettype($input)
            ));
        }

        usort($input, function ($a, $b) use ($property) {
            return strcasecmp($a[$property], $b[$property]);
        });

        return $input;
    }

    /**
     * @param string $expression
     * @param $data
     *
     * @return mixed
     */
    public function jp($expression, $data)
    {
        return ($this->jmesPath)($expression, $data);
    }
}
