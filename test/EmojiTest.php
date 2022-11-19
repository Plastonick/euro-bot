<?php

namespace Plastonick\Test\Euros;

use PHPUnit\Framework\TestCase;
use Plastonick\Euros\Emoji;

class EmojiTest extends TestCase
{
    /**
     * @dataProvider plainTextEmojiProvider
     * @param string $string
     *
     * @return void
     */
    public function testPlainTextStringEmojiFormattedCorrectly(string $string): void
    {
        $fixture = Emoji::createFromString($string);

        self::assertEquals(":{$string}:", $fixture->retrieveRandomEmoji());
    }

    public function plainTextEmojiProvider(): array
    {
        return [
            ['some_emoji'],
            ['emoji'],
            ['emoji123'],
            ['123'],
            ['123some-emoji'],
            ['123some-emoji_wiTH-SoME-CapitAls__'],
        ];
    }

    /**
     * @dataProvider emojiProvider
     * @param string $string
     *
     * @return void
     */
    public function testEmojiStringFormattedCorrectly(string $string): void
    {
        $fixture = Emoji::createFromString($string);

        self::assertEquals($string, $fixture->retrieveRandomEmoji());
    }

    public function emojiProvider(): array
    {
        return [
            ['👀'],
            ['🙈'],
            ['🇳🇵'],
            ['🌚'],
        ];
    }

    public function testToStringReturnsOriginalString(): void
    {
        $string = 'one,emoji,🇰🇷,custom_emoji,🙃';
        $fixture = Emoji::createFromString($string);

        self::assertEquals($string, $fixture->toString());
    }
}
