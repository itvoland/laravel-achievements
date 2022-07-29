<?php
declare(strict_types=1);

namespace ITvoland\Tests\Achievements;

use ITvoland\Achievements\Achievement;

/**
 * Class FirstPost
 *
 * @package ITvoland\Tests\Achievements
 */
class FirstPost extends Achievement
{
    public $name = 'First Post';
    public $description = 'You made your first post!';
}
