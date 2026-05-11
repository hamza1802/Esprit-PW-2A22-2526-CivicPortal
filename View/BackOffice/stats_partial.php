<?php
/**
 * stats_partial.php
 * Statistics Dashboard for BackOffice.
 */
require_once __DIR__ . '/../../controller/UserController.php';
$userStats = UserController::getUserStats();
?>
<section class="page-container" style="padding-top: 2rem;">
    <div class="hero-section" style="margin-bottom: 4rem; border-bottom: 2px solid var(--primary-navy); padding-bottom: 2rem;">
        <h1 style="font-size: 3rem; color: var(--primary-navy); font-weight: 900; letter-spacing: -1.5px;">Portal Analytics</h1>
        <p style="font-size: 1.1rem; opacity: 0.8; margin-top: 10px; font-weight: 600;">Comprehensive overview of user distribution and platform metrics.</p>
    </div>

    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 2.5rem;">
        <!-- Distribution Total Card (Excluding Admin) -->
        <?php 
            $prodTotal = $userStats['agent'] + $userStats['citizen'];
        ?>
        <div class="stat-card" style="background: white; border: 4px solid var(--primary-navy); padding: 3rem; box-shadow: 15px 15px 0px rgba(29, 42, 68, 0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div style="font-size: 1rem; font-weight: 900; text-transform: uppercase; color: var(--primary-navy); opacity: 0.5;">Active Population</div>
                <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(29, 42, 68, 0.1); display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-people-fill" style="font-size: 1.5rem; color: var(--primary-navy);"></i>
                </div>
            </div>
            <div style="font-size: 4.5rem; font-weight: 900; color: var(--primary-navy); line-height: 1;"><?= $prodTotal ?></div>
            <div style="margin-top: 1.5rem; font-weight: 700; color: var(--primary-navy);">Excluding system administrators.</div>
        </div>

        <!-- Circular Role Distribution (Citizen & Agent Only) -->
        <div class="stat-card" style="background: white; border: 4px solid var(--primary-navy); padding: 3rem; box-shadow: 15px 15px 0px rgba(29, 42, 68, 0.1);">
            <div style="font-size: 1rem; font-weight: 900; text-transform: uppercase; color: var(--primary-navy); opacity: 0.5; margin-bottom: 2rem;">Staff vs Citizen Ratio</div>
            
            <div style="display: flex; align-items: center; gap: 3rem;">
                <!-- Donut Chart -->
                <?php 
                    $divTotal = $prodTotal ?: 1;
                    $p1 = ($userStats['agent'] / $divTotal) * 100;
                ?>
                <div style="width: 180px; height: 180px; border-radius: 50%; background: conic-gradient(
                    #3498DB 0% <?= $p1 ?>%, 
                    #2ECC71 <?= $p1 ?>% 100%
                ); position: relative; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                    <div style="width: 70%; height: 70%; border-radius: 50%; background: white; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <span style="font-size: 1.5rem; font-weight: 900; color: var(--primary-navy);"><?= $prodTotal ?></span>
                        <span style="font-size: 0.6rem; font-weight: 900; text-transform: uppercase; opacity: 0.5;">Active</span>
                    </div>
                </div>

                <!-- Legend -->
                <div style="flex-grow: 1; display: flex; flex-direction: column; gap: 1rem;">
                    <div style="display: flex; align-items: center; gap: 0.8rem;">
                        <div style="width: 12px; height: 12px; background: #3498DB; border-radius: 2px;"></div>
                        <div style="flex-grow: 1; font-weight: 800; font-size: 0.85rem;">Staff Agents</div>
                        <div style="font-weight: 900; font-size: 0.9rem; color: #3498DB;"><?= $userStats['agent'] ?></div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.8rem;">
                        <div style="width: 12px; height: 12px; background: #2ECC71; border-radius: 2px;"></div>
                        <div style="flex-grow: 1; font-weight: 800; font-size: 0.85rem;">Citizens</div>
                        <div style="font-weight: 900; font-size: 0.9rem; color: #2ECC71;"><?= $userStats['citizen'] ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
