<?php
declare(strict_types=1);
namespace ITvoland\Achievements;

/**
 * Interface CanAchieve
 *
 * @package ITvoland\Achievements
 */
interface CanAchieve
{
    // Adds a specified amount of points of progress
    public function addProgressToAchiever($achiever, $points);

    // Sets the specified amount of points to this achiever
    public function setProgressToAchiever($achiever, $points);
}
