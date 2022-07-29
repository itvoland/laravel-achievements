<?php
declare(strict_types=1);
namespace ITvoland\Achievements\Event;

use ITvoland\Achievements\Model\AchievementProgress;
use Illuminate\Queue\SerializesModels;

/**
 * Class Progress
 *
 * @package Assada\Achievements\Event
 */
class Progress
{
    use SerializesModels;

    /**
     * @var AchievementProgress
     */
    public $progress;

    /**
     * Create a new event instance.
     *
     * @param AchievementProgress $progress
     */
    public function __construct(AchievementProgress $progress)
    {
        $this->progress = $progress;
    }
}
