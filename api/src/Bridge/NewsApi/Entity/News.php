<?php

namespace App\Bridge\NewsApi\Entity;

use Serializable;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\IEntity;
use App\Entity\Trait\EntityTrait;
use App\Filter\Bridge\SearchFilter;
use App\State\News\NewsProvider;
use DateTime;
use Symfony\Component\Serializer\Annotation\Groups;


#[ApiResource(
    operations: [
        new GetCollection(
            security: "is_granted('PUBLIC_ACCESS')",
            provider: NewsProvider::class
        ),
        new Get(
            provider: NewsProvider::class,
        ),
        // new Get(
        //     controller: NotFoundAction::class,
        //     output: false,
        //     read: false
        // ),
    ],)]
#[ApiFilter(SearchFilter::class)]
class News implements IEntity, Serializable
{

    use EntityTrait;

    #[Groups(["read"])]
    #[ApiProperty(schema: [
        'type' => 'text',
        'example' => "BBC News",
        'writable' => false
    ], iris: "https://schema.org/Text")]
    public ?string $source = null;

    #[Groups(["read"])]
    #[ApiProperty(schema: [
        'type' => 'url',
        'example' => "https://www.facebook.com/bbcnews",
        'writable' => false
    ], iris: "https://schema.org/Text")]
    public ?string $author = null;

    #[Groups(["read"])]
    #[ApiProperty(schema: [
        'type' => 'text',
        'example' => "Cost of living: New prime minister must hit ground running, Crabb says",
        'writable' => false
    ], iris: "https://schema.org/Text")]
    public ?string $title = null;

    #[Groups(["read"])]
    #[ApiProperty(schema: [
        'type' => 'text',
        'example' => "The next prime minister will need to tackle cost of living crisis as priority, ex-Welsh secretary says.",
        'writable' => false
    ], iris: "https://schema.org/Text")]
    public ?string $description = null;

    #[Groups(["read"])]
    #[ApiProperty(schema: [
        'type' => 'url',
        'example' => "https://www.bbc.co.uk/news/uk-wales-politics-62779994",
        'writable' => false
    ], iris: "https://schema.org/Image")]
    public ?string $url = null;

    #[Groups(["read"])]
    #[ApiProperty(schema: [
        'type' => 'url',
        'example' => "https://ichef.bbci.co.uk/news/1024/branded_news/147E2/production/_126583938_capture.jpg",
        'writable' => false
    ], iris: "https://schema.org/Image")]
    public ?string $image = null;

    #[Groups(["read"])]
    #[ApiProperty(schema: [
        'type' => 'datetime',
        'example' => "2022-09-04T10:35:05+00:00",
        'writable' => false
    ], iris: "https://schema.org/DatePublished")]
    public ?DateTime $publishedAt = null;

    #[Groups(["read"])]
    #[ApiProperty(schema: [
        'type' => 'text',
        'example' => "The new prime minister will need to \"hit the ground running\" to tackle the cost of living crisis as a priority, a Conservative MP has said.\r\nStephen Crabb's comments come a day before contenders Liz … [+2006 chars]",
        'writable' => false
    ], iris: "https://schema.org/text")]
    public ?string $content = null;

    public function __construct(array $data = [])
    {
        $this->setEntityData($data);
    }

    public function getId(): ?string
    {
        return $this->id;
    }


    public function getPublishedAt(): ?DateTime
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(string|DateTime|null $dateTime): void
    {
        if (is_string($dateTime)) {
            $dateTime = new DateTime($dateTime);
        }

        $this->publishedAt = $dateTime;
    }

    public function __toString(): string
    {
        return (string)$this->title;
    }

    public function serialize(): string
    {
        return json_encode($this->__serialize(), true);
    }

    public function unserialize(string $data): array
    {
        return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
    }

    public function __serialize(): array
    {
        return get_object_vars($this);
    }

    public function __unserialize(array $data): void
    {
        $this->setEntityData($data);
    }
}
