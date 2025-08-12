<?php

use Google\Service\AdExchangeBuyerII\TimeOfDay;
$user_sub = get_user_active_subscription($_SESSION['user_id'], $pdo);
$user_plan_status='';
if (($user_sub != null || $user_sub != false) && $user_sub['plan_slug'] !== 'free') {
  $user_plan_status='active';
  $today = new DateTime();
  $expires_at = new DateTime($user_sub['expires_at']);
  $interval = $today->diff($expires_at);
  $days_left = (int) $interval->format('%r%a');
} elseif (($user_sub != null || $user_sub != false) && $user_sub['plan_slug']=='free') {
  $user_plan_status = 'free';
  $expires_at = 'بدون تاریخ انقضاء';
  $interval = '0';
  $days_left = '0';
}
//var_dump(value: $user_sub)
?>
<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="py-3 breadcrumb-wrapper mb-4">
    <span class="text-muted fw-light"></span> اشتراک ها و صورت حساب ها 
  </h4>
  <div class="row">
    <div class="col-md-12">
      <div class="card mb-4">
        <!-- Current Plan -->
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 mb-1">
              <div class="mb-4">
                <h6 class="fw-semibold mb-2">اشتراک کنونی شما <span class="badge bg-gradient p-2 fs-3">
                    <h4><?= $user_sub['plan_name'] ?></h6></span> است</h6>
                <p>قیمت کنونی اشتراک: <?= $user_sub['amount_paid'] ?></p>
              </div>
              <div class="mb-4">
                <?php if ($user_sub['plan_slug'] == 'free'): ?>
                  <h6 class="fw-semibold mb-2">بدون تاریخ انقضا</h6>
                  <p>این اشتراک به صورت پیش فرض برای تمام کاربران فعال است.</p>
                  <p>در این اشتراک فقط تعداد محدودی سوال فعال است. جهت استفاده کامل لطفا اشتراک خود را ارتقاء دهید.</p>
                <?php else: ?>
                  <h6 class="fw-semibold mb-2">تاریخ انقضا :</h6>
                  <h4 class="fw-semibold mb-2"><?= $user_sub['expires_at'] ?></h6>
                    <h6 class="fw-semibold mb-2">شما در تاریخ <?= $user_sub['started_at'] ?>این اشتراک را خریداری کرده اید.
                    </h6>

                  <?php endif ?>
              </div>
              <div class="mb-3">
                <p>اشتراک استاندارد برای کسب و کار های کوچک تا متوسط</p>
              </div>
            </div>
            <div class="col-md-6 mb-1">

              <?php if ($user_sub['plan_slug'] == 'free'): ?>
                <div class="alert alert-warning mb-4" role="alert">
                  <h6 class="alert-heading mb-1">جهت استفاده از تمام قابلیت های برنامه، اشتراک خود را ارتقا دید</h6>
                </div>

                <div class="col-md-6 mb-1">
                <?php else: ?>
                  <?php if ($days_left >= 6): ?>
                    <div
                      class="alert alert-<?= $days_left > 20 ? 'success' : ($days_left >= 6 ? 'primary' : 'warning') ?> mb-4"
                      role="alert">
                      <h6 class="alert-heading mb-1">
                        <?php echo " {$days_left} روز تا پایان اشتراک شما باقی مانده است."; ?>
                      </h6>
                    </div>
                  <?php else: ?>
                    <div class="alert alert-warning mb-4 d-none" role="alert">
                      <h6 class="alert-heading mb-1"></h6>
                    </div>
                  <?php endif; ?>

                  <div class="plan-statistics">
                    <div class="d-flex justify-content-between">
                      <span class="fw-semibold mb-2">روز</span>
                      <span class="fw-semibold mb-2">24 از 30 روز</span>
                    </div>
                    <div class="progress">
                      <div class="progress-bar w-75" role="progressbar" aria-valuenow="75" aria-valuemin="0"
                        aria-valuemax="100"></div>
                    </div>
                    <p class="mt-2 mb-0">6 روز باقی مانده تا اشتراک شما نیازمند ارتقا باشد</p>
                  </div>

                <?php endif ?>


              </div>
              <div class="col-12">
                <button class="btn btn-primary me-2 mt-2" data-bs-toggle="modal" data-bs-target="#pricingModal">
                    <?php if($user_plan_status !== 'active' ){echo 'ارتقاء اشتراک';}else{echo 'تغییر اشتراک';}; ?>
              </button>
              <?php if($user_plan_status !== 'free'): ?>

                
                                <button class="btn btn-label-secondary cancel-subscription mt-2">لغو اشتراک</button>

                <?php endif ?>
              </div>
            </div>
          </div>

          <!-- /Current Plan -->
        </div>

        <div class="card">
          <!-- Billing History -->
          <h5 class="card-header border-bottom">تاریخچه صورتحساب</h5>
          <div class="card-datatable table-responsive">
            <table class="invoice-list-table table border-top">
              <thead>
                <tr>
                  <th></th>
                  <th>#شناسه</th>
                  <th><i class="bx bx-trending-up"></i></th>
                  <th>مشتری</th>
                  <th>جمع</th>
                  <th class="text-truncate">تاریخ صدور</th>
                  <th>موجودی</th>
                  <th>وضعیت صورتحساب</th>
                  <th class="cell-fit">عمل‌ها</th>
                </tr>
              </thead>
            </table>
          </div>
          <!--/ Billing History -->
        </div>
      </div>
    </div>

    <!-- Pricing Modal -->
    <div class="modal fade" id="pricingModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-simple modal-pricing">
        <div class="modal-content bg-body p-2 p-md-5">
          <div class="modal-body">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <!-- Pricing Plans -->
            <section class="our-price-plan-area tmp-section-gapTop" id="pricing">
              <div class="container">
                <div class="section-head">
                  <div class="section-sub-title center-title tmp-scroll-trigger tmp-fade-in animation-order-1">
                    <span class="subtitle">جدول اشتراک ها</span>
                  </div>
                  <h2 class="title split-collab tmp-scroll-trigger tmp-fade-in animation-order-2">قیمتگذاری ساده و شفاف
                  </h2>
                  <p>طرحی را انتخاب کنید که به بهترین وجه با نیازهای شما مطابقت داشته باشد. همه طرح‌ها شامل ویژگی‌های
                    اصلی ما هستند.</p>
                </div>

                <div class="row align-items-center">
                  <ul class="pricing_table">

                    <?php
                    // دریافت همه پلن‌ها و ویژگی‌ها
                    $plans = get_all_subscription_plans($pdo);
                    $all_features = get_all_features($pdo);

                    foreach ($plans as $plan):
                      $features = get_plan_features($plan['id'], $pdo);

                      // برای تطبیق سریع ویژگی‌ها
                      $feature_map = [];
                      foreach ($features as $f) {
                        $feature_map[$f['id']] = $f;
                      }

                      // کلاس رنگ پلن
                      $plan_class = '';
                      if (strpos(strtolower($plan['name']), 'gold') !== false) {
                        $plan_class = 'text-gold';
                      } elseif (strpos(strtolower($plan['name']), 'silver') !== false) {
                        $plan_class = 'text-silver';
                      } elseif (strpos(strtolower($plan['name']), 'bronze') !== false) {
                        $plan_class = 'text-bronze';
                      }

                      // قیمت
                      $monthly_price = calculate_plan_price($plan['id'], false, $pdo);
                      ?>

                      <li class="price_block <?= $plan_class ?>">
                        <h3><?= $plan['name'] ?></h3>
                        <div class="price">
                          <div class="price_figure">
                            <span class="price_number"><?= format_price($monthly_price['price']) ?></span>
                          </div>
                        </div>

                        <ul class="features">
                          <?php foreach ($all_features as $feature):
                            $is_enabled = isset($feature_map[$feature['id']]);
                            $value = $is_enabled
                              ? format_feature_value($feature_map[$feature['id']]['feature_value'], $feature_map[$feature['id']]['is_unlimited'])
                              : '-';

                            $icon_class = $is_enabled ? 'enabled-icon' : 'disabled-icon';
                            $icon_html = $is_enabled
                              ? ' <i class="fa fa-check-circle text-success fs-3"></i> '
                              : ' <i class="fa fa-times-circle fs-3"></i> ';
                            ?>
                            <li class="<?= $icon_class ?> text-start">
                              <?= $icon_html ?>     <?= $feature['name'] ?>
                            </li>
                          <?php endforeach; ?>
                        </ul>

                        <div class="footer w-100">
                          <a href="invoice-request.php?plan-id=<?=$plan['id']?>" class="action_button btn btn-primary p-3 fs-3 w-100">خرید</a>
                        </div>
                      </li>

                    <?php endforeach; ?>
                  </ul>
                </div>
              </div>
            </section>
            <!--/ Pricing Plans -->
          </div>
        </div>
      </div>
    </div>
    <!--/ Pricing Modal -->

    <script src="../../assets/js/pages-pricing.js"></script>
  </div>