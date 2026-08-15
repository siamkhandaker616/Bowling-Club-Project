<?php

namespace App\Services\Bowling;

class ScoringEngine
{
    public const MAX_FRAMES = 10;

    public static function validate(array $frames): true|string
    {
        if (count($frames) !== self::MAX_FRAMES) {
            return 'frames_data must contain exactly 10 frames';
        }

        foreach ($frames as $i => $frame) {
            if (! is_array($frame) || ! isset($frame['rolls']) || ! is_array($frame['rolls']) || $frame['rolls'] === []) {
                return "frame ".($i + 1).' must have a non-empty rolls array';
            }

            $rolls = array_values($frame['rolls']);

            if (count($rolls) > 3) {
                return "frame ".($i + 1).' has too many rolls';
            }

            foreach ($rolls as $roll) {
                if (! is_int($roll) && ! ctype_digit((string) $roll)) {
                    return "frame ".($i + 1).' contains a non-numeric roll';
                }
                $roll = (int) $roll;
                if ($roll < 0 || $roll > 10) {
                    return "frame ".($i + 1).' roll outside 0-10';
                }
            }

            $rolls = array_map('intval', $rolls);
            $isLast = $i === self::MAX_FRAMES - 1;

            if ($isLast) {
                $check = self::validateTenthFrame($rolls);
                if ($check !== true) {
                    return "frame 10: $check";
                }
            } else {
                $check = self::validateRegularFrame($rolls);
                if ($check !== true) {
                    return 'frame '.($i + 1).": $check";
                }
            }
        }

        return true;
    }

    protected static function validateRegularFrame(array $rolls): true|string
    {
        $count = count($rolls);

        if ($rolls[0] === 10) {
            if ($count !== 1) {
                return 'a strike must be a single roll';
            }

            return true;
        }

        if ($count !== 2) {
            return 'a non-strike frame needs exactly 2 rolls';
        }

        if ($rolls[0] + $rolls[1] > 10) {
            return 'frame pins exceed 10';
        }

        return true;
    }

    protected static function validateTenthFrame(array $rolls): true|string
    {
        $count = count($rolls);

        if ($count === 2) {
            if ($rolls[0] + $rolls[1] >= 10) {
                return 'a spare or strike in the 10th needs a 3rd roll';
            }

            return true;
        }

        if ($count === 3) {
            $strikeFirst = $rolls[0] === 10;
            $spare = $rolls[0] + $rolls[1] === 10;

            if (! $strikeFirst && ! $spare) {
                return '3rd roll only allowed after a strike or spare';
            }

            return true;
        }

        return 'the 10th frame needs 2 or 3 rolls';
    }

    public static function total(array $frames): int
    {
        $flat = [];

        foreach ($frames as $frame) {
            foreach ($frame['rolls'] as $roll) {
                $flat[] = (int) $roll;
            }
        }

        $score = 0;
        $cursor = 0;

        for ($frame = 0; $frame < self::MAX_FRAMES; $frame++) {
            $roll = $flat[$cursor] ?? 0;

            if ($roll === 10) {
                $score += 10 + ($flat[$cursor + 1] ?? 0) + ($flat[$cursor + 2] ?? 0);
                $cursor++;
            } else {
                $next = $flat[$cursor + 1] ?? 0;
                $sum = $roll + $next;

                if ($sum === 10) {
                    $score += 10 + ($flat[$cursor + 2] ?? 0);
                } else {
                    $score += $sum;
                }

                $cursor += 2;
            }
        }

        return $score;
    }
}
