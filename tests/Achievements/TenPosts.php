<?php
declare(strict_types=1);

namespace ITvoland\Tests\Achievements;

use ITvoland\Achievements\Achievement;

/**
 * Class TenPosts
 *
 * @package ITvoland\Tests\Achievements
 */
class TenPosts extends Achievement
{
    public $name = '10 Posts';
    public $description = 'You have created 10 posts!';
    public $points = 10;
}
