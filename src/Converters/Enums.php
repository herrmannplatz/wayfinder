<?php

namespace Laravel\Wayfinder\Converters;

use Laravel\Ranger\Components\Enum;
use Laravel\Wayfinder\Langs\TypeScript;
use Laravel\Wayfinder\Results\Result;

class Enums extends Converter
{
    public function convert(Enum $enum, bool $useUnionTypes = true): Result
    {
        return $useUnionTypes
            ? $this->convertAsUnion($enum)
            : $this->convertAsEnum($enum);
    }

    public function convertAsEnum(Enum $enum): Result
    {
        $name = str($enum->name)->afterLast('\\')->toString();
        $path = str_replace('\\', '/', $enum->name);

        TypeScript::addFqnToNamespaced(
            $path,
            TypeScript::enum(
                $name,
                collect($enum->cases)
                    ->map(fn($value, $case) => $case." = ".TypeScript::quote($value))
                    ->join(", ")
            )
                ->referenceClass($enum->name, $enum->filePath())
                ->export(),
        );

        $enumLines = [];
        foreach ($enum->cases as $case => $value) {
            $enumLines[] = $case." = ".TypeScript::quote($value).",";
        }

        $content[] = TypeScript::enum(
            $name,
            collect($enum->cases)
                ->map(fn($value, $case) => $case." = ".TypeScript::quote($value))
                ->join(", ")
        )->link($enum->name, $enum->filepath());

        $content[] = '';
        $content[] = TypeScript::block($name)->exportDefault();

        return new Result($path.'.ts', implode(PHP_EOL, $content));
    }

    public function convertAsUnion(Enum $enum): Result
    {
        $name = str($enum->name)->afterLast('\\')->toString();
        $path = str_replace('\\', '/', $enum->name);

        TypeScript::addFqnToNamespaced(
            $path,
            TypeScript::type(
                $name,
                TypeScript::union(
                    collect($enum->cases)
                        ->map(fn ($case) => "'{$case}'")
                        ->values()
                        ->all(),
                ),
            )
                ->referenceClass($enum->name, $enum->filePath())
                ->export(),
        );

        $content = [];

        foreach ($enum->cases as $case => $value) {
            $content[] = TypeScript::constant($case, TypeScript::quote($value))->export();
        }

        $content[] = '';

        $obj = TypeScript::objectWithOnlyKeys(array_keys($enum->cases));

        $content[] = TypeScript::constant($name, $obj)
            ->export()
            ->asConst()
            ->link($enum->name, $enum->filepath());

        $content[] = '';
        $content[] = TypeScript::block($name)->exportDefault();

        return new Result($path.'.ts', implode(PHP_EOL, $content));
    }
}
