<?php
declare(strict_types=1);

namespace ITVOLAND\Tests\AchievementChains;

use ITVOLAND\Achievements\AchievementChain;
use ITVOLAND\Tests\Achievements\FiftyPosts;
use ITVOLAND\Tests\Achievements\FirstPost;
use ITVOLAND\Tests\Achievements\TenPosts;

/**
 * Class PostChain
 *
 * @package ITVOLAND\Tests\AchievementChains
 */
class PostChain extends AchievementChain
{
    public function chain(): array
    {
        return [new FirstPost(), new TenPosts(), new FiftyPosts()];
    }
}
