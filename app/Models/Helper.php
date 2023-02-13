<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Helper
{
    public static function transliterate($name, $lang)
    {
        $result = '';
        foreach (mb_str_split(mb_strtolower($name)) as $char) {
            if ($char == ' ') {
                $result .= '-';
            }
            else {
                if (is_numeric($char)) {
                    $result .= $char;
                }
                elseif (in_array($char, str_split(self::latin_symbols))) {
                    $result .= $char;
                }
                else {
                    if (isset(self::accords[$lang][$char])) {
                        $result .= self::accords[$lang][$char];
                    }
                }
            }
        }
        return preg_replace('/-+/', '-', $result);;
    }

    const latin_symbols = 'abcdefghijklmnopqrstuvwxyz';

    const accords = [
        'uk' => [
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'ґ' => 'g',
            'д' => 'd',
            'е' => 'e',
            'є' => 'je',
            'ж' => 'zh',
            'з' => 'z',
            'и' => 'y',
            'і' => 'i',
            'ї' => 'ji',
            'й' => 'j',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ф' => 'f',
            'х' => 'h',
            'ц' => 'ts',
            'ч' => 'ch',
            'ш' => 'sh',
            'щ' => 'shch',
            'ь' => '',
            'ю' => 'ju',
            'я' => 'ja',
        ],
        'ru' => [
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'д' => 'd',
            'е' => 'je',
            'ё' => 'jo',
            'ж' => 'zh',
            'з' => 'z',
            'и' => 'i',
            'й' => 'j',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ф' => 'f',
            'х' => 'h',
            'ц' => 'ts',
            'ч' => 'ch',
            'ш' => 'sh',
            'щ' => 'shch',
            'ь' => '',
            'ы' => 'y',
            'ъ' => '',
            'э' => 'e',
            'ю' => 'ju',
            'я' => 'ja',
        ],
    ];
}
