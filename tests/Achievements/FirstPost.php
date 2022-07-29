<?php
declare(strict_types=1);

namespace ITVOLAND\Tests\Achievements;

use ITVOLAND\Achievements\Achievement;

/**
 * Class FirstPost
 *
 * @package ITVOLAND\Tests\Achievements
 */
class FirstPost extends Achievement
{
    public $name = 'First Post';
    public $description = 'You made your first post!';
}
