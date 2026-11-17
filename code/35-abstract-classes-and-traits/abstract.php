<?php

declare(strict_types=1);

// An abstract class: a parent with a gap that each child must fill.
abstract class Notice
{
    public function __construct(protected readonly string $name) {}

    // The same for every notice: final, so no child can change it.
    final public function text(): string
    {
        return "Dear $this->name,\n" . $this->body() . "\nYour library\n";
    }

    abstract protected function body(): string;
}

final class Welcome extends Notice
{
    protected function body(): string
    {
        return 'Welcome! You can borrow up to three books at a time.';
    }
}

final class Reminder extends Notice
{
    public function __construct(string $name, private readonly string $title)
    {
        parent::__construct($name);
    }

    protected function body(): string
    {
        return "\"$this->title\" is due back in three days.";
    }
}

echo (new Welcome('Amira'))->text(), "\n";
echo (new Reminder('Kenji', 'Kitchen'))->text();
