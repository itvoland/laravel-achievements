<?php
declare(strict_types=1);

namespace ITVOLAND\Tests;

use ITVOLAND\Achievements\Achievement;
use ITVOLAND\Achievements\Model\AchievementDetails;
use ITVOLAND\Tests\Model\User;
use ITVOLAND\Tests\Achievements\FirstPost;
use ITVOLAND\Tests\Achievements\TenPosts;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Class AchievementTest
 *
 * @package ITVOLAND\Tests
 */
class AchievementTest extends DBTestCase
{
    public $users;
    public $onePost;
    public $tenPosts;

    public function setUp():void
    {
        parent::setUp();
        $this->users[] = User::find(1);
        $this->users[] = User::find(2);
        $this->users[] = User::find(3);
        $this->users[] = User::find(4);
        $this->users[] = User::find(5);

        $this->onePost = new FirstPost();
        $this->tenPosts = new TenPosts();
    }


    public function testUnlocked()
    {
        /* Testing for sync disabled */
        $this->app['config']->set('achievements.locked_sync', false);
        $this->assertEquals(0, $this->users[0]->achievements->count());
        $unlocked = AchievementDetails::getUnsyncedByAchiever($this->users[0])->get();
        $this->assertEquals(2, $unlocked->count());

        $this->users[0]->unlock($this->onePost);
        $this->users[0] = $this->users[0]->fresh();

        $unlocked = AchievementDetails::getUnsyncedByAchiever($this->users[0])->get();
        $this->assertEquals(1, $unlocked->count());

        /* Testing for sync enabled */
        $this->assertEquals(0, $this->users[1]->achievements->count());
        $this->app['config']->set('achievements.locked_sync', true);
        $this->users[1] = $this->users[1]->fresh();
        $this->assertEquals(2, $this->users[1]->achievements->count());
    }

    /**
     * Tests the setup
     */
    public function testSetup()
    {

        // Tests whether the Achievements:all() shortcut is working
        $this->assertEquals(2, Achievement::all()->count());

        // For users, check only names.
        // Achiever classes don't matter much. They just need to exist and have IDs.
        $this->assertEquals($this->users[0]->name, 'Gamer0');
        $this->assertEquals($this->users[1]->name, 'Gamer1');
        $this->assertEquals($this->users[2]->name, 'Gamer2');
        $this->assertEquals($this->users[3]->name, 'Gamer3');
        $this->assertEquals($this->users[4]->name, 'Gamer4');

        // Loads the AchievementDetails Models

        /** @var AchievementDetails $onePostDB */
        $onePostDB  = AchievementDetails::find(1);
        /** @var AchievementDetails $tenPostsDB */
        $tenPostsDB = AchievementDetails::find(2);

        // Compare Model data with class data for OnePost
        $this->assertNotNull($onePostDB);

        $this->assertEquals($onePostDB->name, $this->onePost->name);
        $this->assertEquals($onePostDB->description, $this->onePost->description);
        $this->assertEquals($onePostDB->points, $this->onePost->points);

        // Compare Model data with class data for TenPosts
        $this->assertNotNull($tenPostsDB);

        $this->assertEquals($tenPostsDB->name, $this->tenPosts->name);
        $this->assertEquals($tenPostsDB->description, $this->tenPosts->description);
        $this->assertEquals($tenPostsDB->points, $this->tenPosts->points);

        // Compares conversion between class instance and class data
        $this->assertEquals($onePostDB->getClass()->getClassName(), $this->onePost->getClassName());
        $this->assertEquals($tenPostsDB->getClass()->getClassName(), $this->tenPosts->getClassName());
    }

    /**
     * Tests unlocking achievements.
     */
    public function testUnlock()
    {

        // First user: unlocks both achievements.
        $this->users[0]->unlock($this->onePost);
        $this->users[0]->unlock($this->tenPosts);

        // Second user: unlock only first achievement.
        $this->users[1]->unlock($this->onePost);

        // Third user: unlock only second achievement.
        $this->users[2]->unlock($this->tenPosts);

        $onePostModel  = $this->onePost->getModel();
        $tenPostsModel = $this->tenPosts->getModel();

        // Assertions via checking the database
        $this->assertEquals($onePostModel->unlocks()->count(), 2);
        $this->assertEquals($tenPostsModel->unlocks()->count(), 2);

        $onePostFirstUnlocked = $onePostModel->unlocks()->first();
        $onePostLastUnlocked = $onePostModel->unlocks()->last();

        $this->assertEquals($onePostFirstUnlocked->achiever_id, $this->users[0]->id);
        $this->assertEquals($onePostFirstUnlocked->achiever_type, get_class($this->users[0]));

        $this->assertEquals($onePostLastUnlocked->achiever_id, $this->users[1]->id);
        $this->assertEquals($onePostLastUnlocked->achiever_type, get_class($this->users[1]));

        // Checking via the hasUnlocked method
        $this->assertTrue($this->users[0]->hasUnlocked($this->onePost));
        $this->assertTrue($this->users[1]->hasUnlocked($this->onePost));
        $this->assertFalse($this->users[2]->hasUnlocked($this->onePost));
        $this->assertFalse($this->users[3]->hasUnlocked($this->onePost));
        $this->assertFalse($this->users[4]->hasUnlocked($this->onePost));

        $this->assertTrue($this->users[0]->hasUnlocked($this->tenPosts));
        $this->assertFalse($this->users[1]->hasUnlocked($this->tenPosts));
        $this->assertTrue($this->users[2]->hasUnlocked($this->tenPosts));
        $this->assertFalse($this->users[3]->hasUnlocked($this->tenPosts));
        $this->assertFalse($this->users[4]->hasUnlocked($this->tenPosts));
    }

    /**
     * Test adding/removing/progressing on achievements.
     */
    public function testProgress()
    {
        $this->users[1]->addProgress($this->tenPosts, 10);
        $this->users[3]->addProgress($this->tenPosts, 9);
        $this->users[4]->addProgress($this->tenPosts, 3);

        // After adding these progresses, user 1 should have unlocked tenPosts.
        $this->assertTrue($this->users[1]->hasUnlocked($this->tenPosts));
        $this->assertFalse($this->users[3]->hasUnlocked($this->tenPosts));
        $this->assertFalse($this->users[4]->hasUnlocked($this->tenPosts));

        // Add a progress to users 3 and check whether they have unlocked tenPosts.
        $this->users[3]->addProgress($this->tenPosts, 1);
        $this->assertTrue($this->users[3]->hasUnlocked($this->tenPosts));

        // Add a progress to users 4 and check whether they still didn't unlock tenPosts.
        $this->users[4]->addProgress($this->tenPosts, 3);
        $this->assertFalse($this->users[4]->hasUnlocked($this->tenPosts));

        // Check the current progress on user 4. Should be 6.
        $this->assertEquals(6, $this->users[4]->achievementStatus($this->tenPosts)->points);

        // Remove one point, check unlocked and check progress. Should be 5.
        $this->users[4]->removeProgress($this->tenPosts, 1);
        $this->assertFalse($this->users[4]->hasUnlocked($this->tenPosts));
        $this->assertEquals(5, $this->users[4]->achievementStatus($this->tenPosts)->points);

        // Reset progress, check unlocked and check progress. Should be 0.
        $this->users[4]->resetProgress($this->tenPosts);
        $this->assertFalse($this->users[4]->hasUnlocked($this->tenPosts));
        $this->assertEquals(0, $this->users[4]->achievementStatus($this->tenPosts)->points);

        // Set progress to 2, check unlocked and check progress. Should be 2.
        $this->users[4]->setProgress($this->tenPosts, 2);
        $this->assertFalse($this->users[4]->hasUnlocked($this->tenPosts));
        $this->assertEquals(2, $this->users[4]->achievementStatus($this->tenPosts)->points);

        // Reset progress on this achievement for all users. They should remain unlocked with points intact.
        $this->users[1]->resetProgress($this->tenPosts);
        $this->users[3]->resetProgress($this->tenPosts);
        $this->users[4]->resetProgress($this->tenPosts);

        $this->assertTrue($this->users[1]->hasUnlocked($this->tenPosts));
        $this->assertTrue($this->users[3]->hasUnlocked($this->tenPosts));
        $this->assertFalse($this->users[4]->hasUnlocked($this->tenPosts));

        $this->assertEquals(10, $this->users[1]->achievementStatus($this->tenPosts)->points);
        $this->assertEquals(10, $this->users[3]->achievementStatus($this->tenPosts)->points);
        $this->assertEquals(0, $this->users[4]->achievementStatus($this->tenPosts)->points);
    }

    /**
     * Tests all relationship methods on Achiever trait.
     */
    public function testRelationsLockedUnsynced()
    {
        // Setup
        $this->app['config']->set('achievements.locked_sync', false);
        $this->users[0]->unlock($this->onePost);
        $this->users[0]->unlock($this->tenPosts);

        $this->users[1]->unlock($this->onePost);
        $this->users[1]->setProgress($this->tenPosts, 4);

        $this->users[2]->unlock($this->onePost);

        $this->users[3]->setProgress($this->tenPosts, 5);

        $this->users[0] = $this->users[0]->fresh();
        $this->users[1] = $this->users[1]->fresh();
        $this->users[2] = $this->users[2]->fresh();
        $this->users[3] = $this->users[3]->fresh();

        $this->assertEquals(2, $this->users[0]->achievements->count());
        $this->assertEquals(2, $this->users[1]->achievements->count());
        $this->assertEquals(1, $this->users[2]->achievements->count());
        $this->assertEquals(1, $this->users[3]->achievements->count());

        $this->assertEquals(0, $this->users[0]->lockedAchievements()->count());
        $this->assertEquals(1, $this->users[1]->lockedAchievements()->count());
        $this->assertEquals(1, $this->users[2]->lockedAchievements()->count());
        $this->assertEquals(2, $this->users[3]->lockedAchievements()->count());

        $this->assertEquals(2, $this->users[0]->unlockedAchievements()->count());
        $this->assertEquals(1, $this->users[1]->unlockedAchievements()->count());
        $this->assertEquals(1, $this->users[2]->unlockedAchievements()->count());
        $this->assertEquals(0, $this->users[3]->unlockedAchievements()->count());

        $this->assertEquals(0, $this->users[0]->inProgressAchievements()->count());
        $this->assertEquals(1, $this->users[1]->inProgressAchievements()->count());
        $this->assertEquals(0, $this->users[2]->inProgressAchievements()->count());
        $this->assertEquals(1, $this->users[3]->inProgressAchievements()->count());
    }

    /**
     * Tests all relationship methods on Achiever trait.
     */
    public function testRelationsLockedSynced()
    {
        // Setup
        $this->app['config']->set('achievements.locked_sync', true);
        $this->users[0]->unlock($this->onePost);
        $this->users[0]->unlock($this->tenPosts);

        $this->users[1]->unlock($this->onePost);
        $this->users[1]->setProgress($this->tenPosts, 4);

        $this->users[2]->unlock($this->onePost);

        $this->users[3]->setProgress($this->tenPosts, 5);

        $this->users[0] = $this->users[0]->fresh();
        $this->users[1] = $this->users[1]->fresh();
        $this->users[2] = $this->users[2]->fresh();
        $this->users[3] = $this->users[3]->fresh();

        $this->assertEquals(2, $this->users[0]->achievements->count());
        $this->assertEquals(2, $this->users[1]->achievements->count());
        $this->assertEquals(2, $this->users[2]->achievements->count());
        $this->assertEquals(2, $this->users[3]->achievements->count());

        $this->assertEquals(0, $this->users[0]->lockedAchievements()->count());
        $this->assertEquals(1, $this->users[1]->lockedAchievements()->count());
        $this->assertEquals(1, $this->users[2]->lockedAchievements()->count());
        $this->assertEquals(2, $this->users[3]->lockedAchievements()->count());

        $this->assertEquals(2, $this->users[0]->unlockedAchievements()->count());
        $this->assertEquals(1, $this->users[1]->unlockedAchievements()->count());
        $this->assertEquals(1, $this->users[2]->unlockedAchievements()->count());
        $this->assertEquals(0, $this->users[3]->unlockedAchievements()->count());

        $this->assertEquals(0, $this->users[0]->inProgressAchievements()->count());
        $this->assertEquals(1, $this->users[1]->inProgressAchievements()->count());
        $this->assertEquals(0, $this->users[2]->inProgressAchievements()->count());
        $this->assertEquals(1, $this->users[3]->inProgressAchievements()->count());
    }

    public function testUnlockedWithMorphMap()
    {
        Relation::morphMap([
            'user' => User::class
        ]);

        $user = $this->users[0];
        $user->unlock($this->tenPosts);

        $user = $user->fresh();

        $unlocked = AchievementDetails::getUnsyncedByAchiever($user)->get();
        $this->assertEquals(1, $unlocked->count());
    }

    public function testAchieverMorphMap()
    {
        Relation::morphMap([
            'user' => User::class
        ]);

        $user = $this->users[0];

        $user->unlock($this->onePost);

        $user = $user->fresh();
        $onePostModel  = $this->onePost->getModel();

        $onePostFirstUnlocked = $onePostModel->unlocks()->first();

        $this->assertEquals($onePostFirstUnlocked->achiever_id, $user->id);
        $this->assertEquals($onePostFirstUnlocked->achiever_type, 'user');

        $progress = $this->onePost->getOrCreateProgressForAchiever($user);

        $this->assertEquals($onePostFirstUnlocked->id, $progress->id);
    }

    public function testAchievementProgressMapToDetails()
    {
        $this->users[0]->unlock($this->onePost);
        $this->users[0]->unlock($this->tenPosts);

        foreach($this->users[0]->unlockedAchievements() as $unlockedAchievement) {
            $this->assertEquals($unlockedAchievement->name, $unlockedAchievement->details->name);
            $this->assertEquals($unlockedAchievement->description, $unlockedAchievement->details->description);
        }
    }
}
