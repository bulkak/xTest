<?php
declare(strict_types=1);

namespace xTest\Entity;

use xTest\Entity\Attributes\Identifier;

final class User implements Entity
{
    #[Identifier]
    public int $id;
    public string $name;
    public string $email;
    public \DateTime $created;
    public ?\DateTime $deleted = null;
    public ?string $notes;
}
