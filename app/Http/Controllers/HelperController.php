<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

class HelperController extends Controller
{
    /**
     * Converts each line of the given text into a paragraph element.
     *
     * This function splits the input text into individual lines and wraps each line
     * in a paragraph (`<p>`) tag. If a string of classes is provided, it adds them
     * as the class attribute of the paragraph tags.
     *
     * @param  string  $text  The text to be converted into paragraphs.
     * @param  string|null  $classes  Optional string of classes to be added to each paragraph tag.
     * @return string The converted text with each line wrapped in a paragraph tag.
     *                If classes are provided, each paragraph tag will include them.
     */
    public static function linesToParagraphs(string $text, ?string $classes = null): string
    {
        $lines = explode("\n", trim($text));
        $paragraphs = array_map(function ($line) use ($classes) {
            $classAttribute = $classes ? " class='".e($classes)."'" : '';

            return '<p'.$classAttribute.'>'.e($line).'</p>';
        }, $lines);

        return implode('', $paragraphs);
    }

    /**
     * Returns the end date of a given period.
     *
     * This function calculates the end date of a period based on the latest date
     * and the number of days from today. If the calculated end date is greater
     * than the latest date, the latest date is returned instead.
     *
     * @param  string  $latestDate  The latest date of the period.
     * @param  int  $numDaysFromToday  The number of days from today to calculate the end date.
     * @return Carbon The end date of the period.
     */
    public static function getEndDate($latestDate, $numDaysFromToday): Carbon
    {
        $latestDate = Carbon::parse($latestDate);
        $end = now()->addDays($numDaysFromToday);

        return $end->greaterThan($latestDate) ? $latestDate : $end;
    }

    /**
     * Update the .env file with the given key-value pairs.
     * 
     * @param array $data Key-value pairs to update
     * @param bool $create If true, creates variables that don't exist. Default: false
     */
    public static function updateEnv(array $data, bool $create = false): void
    {
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        foreach ($data as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            if (preg_match($pattern, $envContent)) {
                // Update existing variable
                $envContent = preg_replace($pattern, "{$key}=\"{$value}\"", $envContent);
            } elseif ($create) {
                // Create new variable if it doesn't exist and $create is true
                $envContent .= "\n{$key}=\"{$value}\"";
            }
        }

        file_put_contents($envPath, $envContent);
    }
}
