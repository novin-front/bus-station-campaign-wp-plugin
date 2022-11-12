<div class="wrap">
  <h1 class="wp-heading-inline">
    لیست کاربران</h1>

  <a href="<?php echo add_query_arg(["action" => "add"]) ?>" class="page-title-action">افزودن کاربر جدید</a>
  <hr class="wp-header-end">

  <p class="search-box">
    <label class="screen-reader-text" for="post-search-input">جست‌وجوی کاربران:</label>
    <input type="search" id="post-search-input" name="s" value="">
    <input type="submit" id="search-submit" class="button" value="جست‌وجوی بر اساس نام کاربر">
  </p>

  <input type="hidden" name="post_status" class="post_status_page" value="all">
  <input type="hidden" name="post_type" class="post_type_page" value="post">



  <input type="hidden" id="_wpnonce" name="_wpnonce" value="99b6af641f"><input type="hidden" name="_wp_http_referer" value="/wordpress.test/wp-admin/edit.php">
  <div class="tablenav top">

    <!-- <div class="alignleft actions bulkactions">
        <label for="bulk-action-selector-top" class="screen-reader-text">انتخاب کار دسته جمعی</label><select name="action" id="bulk-action-selector-top">
          <option value="-1">کارهای دسته‌جمعی</option>
          <option value="edit" class="hide-if-no-js">ویرایش</option>
          <option value="trash">انتقال به زباله‌دان</option>
        </select>
        <input type="submit" id="doaction" class="button action" value="اجرا">
      </div> -->
    <!-- <div class="alignleft actions">
        <label for="filter-by-date" class="screen-reader-text">صافی براساس تاریخ</label>
        <select name="m" id="filter-by-date">
          <option selected="selected" value="0">همهٔ تاریخ‌ها</option>
          <option value="202111">نوامبر 2021</option>
          <option value="202110">اکتبر 2021</option>
        </select>
        <label class="screen-reader-text" for="cat">صافی با دسته‌بندی</label><select name="cat" id="cat" class="postform">
          <option value="0">همه دسته‌ها</option>
          <option class="level-0" value="1">دسته‌بندی نشده</option>
        </select>
        <input type="submit" name="filter_action" id="post-query-submit" class="button" value="صافی">
      </div> -->
    <!-- <div class="tablenav-pages one-page"><span class="displaying-num">3 مورد</span>
        <span class="pagination-links"><span class="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>
          <span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>
          <span class="paging-input"><label for="current-page-selector" class="screen-reader-text">برگهٔ فعلی</label><input class="current-page" id="current-page-selector" type="text" name="paged" value="1" size="1" aria-describedby="table-paging"><span class="tablenav-paging-text"> از <span class="total-pages">1</span></span></span>
          <span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>
          <span class="tablenav-pages-navspan button disabled" aria-hidden="true">»</span></span>
      </div> -->
    <br class="clear">
  </div>
  <h2 class="screen-reader-text">فهرست کاربران</h2>
  <table class="wp-list-table widefat fixed striped table-view-list posts">
    <thead>
      <tr>
        <td id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1">انتخاب همه</label><input id="cb-select-all-1" type="checkbox"></td>
        <th scope="col" id="title" class="manage-column column-title column-primary sortable desc"><a href=""><span>نام و نام خانوادگی</span><span class="sorting-indicator"></span></a></th>
        <th scope="col" id="categories" class="manage-column column-categories">شماره تلفن</th>
        <th scope="col" id="tags" class="manage-column column-tags">نوع مهاجرت</th>
        <th scope="col" id="date" class="manage-column column-date sortable asc"><a href=""><span>تاریخ ثبت فرم</span><span class="sorting-indicator"></span></a></th>
        <!-- <th scope="col" id="date" class="manage-column column-date sortable asc"><a href="">عملیات</th> -->
      </tr>
    </thead>

    <tbody id="the-list">
      <?php foreach ($users_list as $user) : ?>
        <tr id="post-27" class="iedit author-self level-0 post-27 type-post status-publish format-standard hentry category-1 entry">
          <th scope="row" class="check-column"> <label class="screen-reader-text" for="cb-select-<?php echo $user->id; ?>">
              <?php echo $user->first_name . " " . $user->last_name; ?></label>
            <input id="cb-select-<?php echo $user->id; ?>" type="checkbox" name="post[]" value="<?php echo $user->id; ?>">
          </th>
          <td class="title column-title has-row-actions column-primary page-title" data-colname="نام و نام خانوادگی">
            <?php echo $user->full_name; ?> </label>
          </td>
          <td class="categories column-categories" data-colname="موبایل">
            <?php echo $user->mobile; ?>
          </td>
          <td class="tags column-tags" data-colname="نوع مهاجرت">
            <?php echo $user->emigration_type; ?>
          </td>
          <td class="date column-date" data-colname="تاریخ">تکمیل شده در<br>
            <?php echo $user->create_at; ?>
          </td>
        </tr>
      <?php endforeach; ?>

    </tbody>

    <tfoot>
      <tr>
        <td id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1">انتخاب همه</label><input id="cb-select-all-1" type="checkbox"></td>
        <th scope="col" id="title" class="manage-column column-title column-primary sortable desc"><a href=""><span>نام و نام خانوادگی</span><span class="sorting-indicator"></span></a></th>
        <th scope="col" id="categories" class="manage-column column-categories">شماره تلفن</th>
        <th scope="col" id="tags" class="manage-column column-tags">نوع مهاجرت</th>
        <th scope="col" id="date" class="manage-column column-date sortable asc"><a href=""><span>تاریخ ثبت فرم</span><span class="sorting-indicator"></span></a></th>
        <!-- <th scope="col" id="date" class="manage-column column-date sortable asc"><a href="">عملیات</th> -->
      </tr>
    </tfoot>

  </table>
  <div class="tablenav bottom">

    <!-- <div class="alignleft actions bulkactions">
        <label for="bulk-action-selector-bottom" class="screen-reader-text">انتخاب کار دسته جمعی</label><select name="action2" id="bulk-action-selector-bottom">
          <option value="-1">کارهای دسته‌جمعی</option>
          <option value="edit" class="hide-if-no-js">ویرایش</option>
          <option value="trash">انتقال به زباله‌دان</option>
        </select>
        <input type="submit" id="doaction2" class="button action" value="اجرا">
      </div> -->


    <div class="tablenav bottom">

      <!-- <div class="alignleft actions bulkactions">
        <label for="bulk-action-selector-bottom" class="screen-reader-text">انتخاب کار دسته‌جمعی</label><select name="action2" id="bulk-action-selector-bottom">
          <option value="-1">کارهای دسته‌جمعی</option>
          <option value="bulk_force_regenerate_thumbnails">Force Regenerate Thumbnails</option>
          <option value="delete">پاک کردن‌ برای همیشه</option>
        </select>
        <input type="submit" id="doaction2" class="button action" value="اجرا">
      </div> -->


      <div class="tablenav-pages"><span class="displaying-num"><?php echo $total; ?> مورد</span>
        <span class="pagination-links"><span class="tablenav-pages-navspan" aria-hidden="true">«</span>
          <span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
          <span class="screen-reader-text">برگه‌ی کنونی</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">1 از <span class="total-pages"><?php echo $total; ?></span></span></span>
          <a class="next-page" href="<?php echo add_query_arg(["paged" => $paged+1]) ?>"><span class="screen-reader-text">برگه بعد</span><span aria-hidden="true">›</span></a>
          <a class="last-page" href="<?php echo add_query_arg(["paged" => (round($total / 10 ))]) ?>"><span class="screen-reader-text">برگه آخر</span><span aria-hidden="true">»</span></a></span>
      </div>
      <br class="clear">
    </div>





    <div class="alignleft actions">
    </div>
    <br class="clear">
  </div>

  <div id="ajax-response"></div>
  <div class="clear"></div>
</div>