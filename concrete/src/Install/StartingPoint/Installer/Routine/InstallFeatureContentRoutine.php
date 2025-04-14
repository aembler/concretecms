<?php
namespace Concrete\Core\Install\StartingPoint\Installer\Routine;

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class InstallFeatureContentRoutine extends AbstractRoutine
{

    /**
     * @var string
     */
    protected $text;

    /**
     * @var string
     */
    protected $domain;

    /**
     * @var string
     */
    protected $feature;

    public function __construct(?string $domain = null, ?string $feature = null, ?string $text = null)
    {
        $this->text = $text;
        $this->domain = $domain;
        $this->feature = $feature;
    }

    public function setText(?string $text): void
    {
        $this->text = $text;
    }

    public function setDomain(string $domain): void
    {
        $this->domain = $domain;
    }

    public function setFeature(?string $feature): void
    {
        $this->feature = $feature;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @return string
     */
    public function getFeature(): ?string
    {
        return $this->feature;
    }

    public function getContentFile(): string
    {
        if ($this->getFeature() && $this->getDomain()) {
            return sprintf(DIR_BASE_CORE . '/features/%s/%s.xml', $this->getDomain(), $this->getFeature());
        } else {
            return sprintf(DIR_BASE_CORE . '/features/%s.xml', $this->getDomain());
        }
    }

    public function hasContentFile(): bool
    {
        $filesystem = new Filesystem();
        $file = $this->getContentFile();
        return $filesystem->exists($file);
    }

    public function denormalize(DenormalizerInterface $denormalizer, $data, ?string $format = null, array $context = [])
    {
        parent::denormalize($denormalizer, $data, $format, $context);
        $this->text = $data['text'];
        $this->domain = $data['domain'];
        $this->feature = $data['feature'];
    }

    public function normalize(NormalizerInterface $normalizer, ?string $format = null, array $context = [])
    {
        $data = parent::normalize($normalizer, $format, $context);
        $data['text'] = $this->text;
        $data['domain'] = $this->domain;
        $data['feature'] = $this->feature;
        return $data;
    }

}
