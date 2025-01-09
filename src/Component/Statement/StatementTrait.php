<?php

namespace Misery\Component\Statement;

use Misery\Component\Action\ActionInterface;
use Misery\Component\Common\Options\OptionsInterface;

trait StatementTrait
{
    /** @var ActionInterface */
    private $action;
    private $context;
    private $conditions = [];
    private $key = 0;

    private $template = [
        'or' => null,
        'and' => null,
        'when' => null,
        'then' => null,
    ];

    private function __construct()
    {
    }

    public static function prepare(ActionInterface $action, array $context = []): StatementInterface
    {
        $self = new self();
        $self->action = $action;
        $self->context = $context;

        return $self;
    }

    public function when(string $field, string $value = null): StatementInterface
    {
        $this->key++;

        $this->conditions[$this->key] = ['when' => new Field($field, $value)];

        return $this;
    }

    public function or(string $field, string $value = null): StatementInterface
    {
        $this->conditions[$this->key] = $this->conditions[$this->key] + ['or' => new Field($field, $value)];

        return $this;
    }

    public function and(string $field, string $value = null): StatementInterface
    {
        $this->conditions[$this->key] = $this->conditions[$this->key] + ['and' => new Field($field, $value)];

        return $this;
    }

    public function then(string $field, $value = null): void
    {
        if (isset($this->conditions[$this->key])) {
            $this->conditions[$this->key]['then'][] = new Field($field, $value);
        }
    }

    public function isApplicable(array $item): bool
    {
        return count($this->conditions) === count(array_filter($this->conditions, function ($condition) use ($item) {
                $condition = array_merge($this->template, $condition);
                switch (true) {
                    case !empty($condition['or']) && (true === $this->whenField($condition['when'],
                                $item) || true === $this->whenField($condition['or'], $item)):
                    case !empty($condition['and']) && (true === $this->whenField($condition['when'],
                                $item) && true === $this->whenField($condition['and'], $item)):
                    case true === $this->whenField($condition['when'], $item):
                        return true;
                    default:
                        return false;
                }
            }));
    }

    public function apply(array $item): array
    {
        foreach ($this->conditions as $condition) {
            $condition = array_merge($this->template, $condition);
            switch (true) {
                case !empty($condition['or']) && (true === $this->whenField($condition['when'],
                            $item) || true === $this->whenField($condition['or'], $item)):
                case !empty($condition['and']) && (true === $this->whenField($condition['when'],
                            $item) && true === $this->whenField($condition['and'], $item)):
                case empty($condition['and']) && empty($condition['or']) && true === $this->whenField($condition['when'],
                        $item):
                    foreach ($condition['then'] as $thenCondition) {
                        $item = $this->thenField($thenCondition, $item);
                    }
                    break;
                default:
                    break;
            }
        }

        return $item;
    }

    private function thenField(Field $field, array $item): array
    {
        if ($this->action instanceof OptionsInterface) {
            $this->action->setOptions([
                    'key' => $field->getField(),
                    'field' => $field->getField(),
                    'value' => $field->getValue(),
                ] + $this->context);

            return $this->action->apply($item);
        }

        return $item;
    }

    public function setAction(ActionInterface $action): void
    {
        $this->action = $action;
    }
}