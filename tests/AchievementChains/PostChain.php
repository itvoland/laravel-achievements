<?php
declare(strict_types=1);

namespace ITvoland\Tests\AchievementChains;

use ITvoland\Achievements\AchievementChain;
use ITvoland\Tests\Achievements\FiftyPosts;
use ITvoland\Tests\Achievements\FirstPost;
use ITvoland\Tests\Achievements\TenPosts;

/**
 * Class PostChain
 *
 * @package ITvoland\Tests\AchievementChains
 */
class PostChain extends AchievementChain
{
    public function chain(): array
    {
        return [new FirstPost(), new TenPosts(), new FiftyPosts()];
    }
}
