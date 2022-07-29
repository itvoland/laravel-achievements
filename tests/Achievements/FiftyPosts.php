<?php
declare(strict_types=1);

namespace ITvoland\Tests\Achievements;

use ITvoland\Achievements\Achievement;

/**
 * Class FiftyPosts
 *
 * @package ITvoland\Tests\Achievements
 */
class FiftyPosts extends Achievement
{
    public $name = 'Fifty Posts';
    public $description = 'You have created 50 posts!';
    public $points = 50;
}
