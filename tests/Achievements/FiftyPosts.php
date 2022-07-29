<?php
declare(strict_types=1);

namespace ITVOLAND\Tests\Achievements;

use ITVOLAND\Achievements\Achievement;

/**
 * Class FiftyPosts
 *
 * @package ITVOLAND\Tests\Achievements
 */
class FiftyPosts extends Achievement
{
    public $name = 'Fifty Posts';
    public $description = 'You have created 50 posts!';
    public $points = 50;
}
