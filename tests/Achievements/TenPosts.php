<?php
declare(strict_types=1);

namespace ITVOLAND\Tests\Achievements;

use ITVOLAND\Achievements\Achievement;

/**
 * Class TenPosts
 *
 * @package ITVOLAND\Tests\Achievements
 */
class TenPosts extends Achievement
{
    public $name = '10 Posts';
    public $description = 'You have created 10 posts!';
    public $points = 10;
}
