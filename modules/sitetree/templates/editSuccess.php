<?php
$sitetree = $form->getObject();

slot('breadcrumbs', get_partial('sitetree/breadcrumbs', array('sitetree' => $sitetree, 'editNode' => true)));

$isNew = !($sitetree->id > 0);
$culture = $sf_user->getCulture();
?>

<div id="sf_admin_container">

  <h1>Edit '<?php echo $sitetree->getTitle(); ?>' properties</h1>

  <div id="sf_admin_header">
    <p>This allows you to edit the information about this sitetree node.
    
    <?php if ($sitetree->isManagedModule()) 
    {
      echo ' You can also ' . link_to('edit the content for this page', $sitetree->getEditLink());
    } ?>
    </p>
    
    <?php if (!$sf_user->isSuperAdmin()) 
    {
      echo '<p>You are not a super administrator, so you cannot lock or unlock nodes.</p>';
    } ?>
  </div>
  
  <?php if ($form->hasErrors()) : ?>
    <div class="error">The sitetree node has not been saved due to some errors.</div>
  <?php endif; ?>

  <div id="sf_admin_content">
    <div class="sf_admin_form">

    	<?php echo $form->renderFormTag(url_for('sitetree/edit'.(!$isNew ? '?id='.$sitetree->getId() : ''))); ?>
        <?php 
        echo $form->renderGlobalErrors(); 
        echo $form->renderHiddenFields();
        ?>
      
    		<fieldset id="sf_fieldset_none">
          <div class="sf_admin_form_row">
    				<div>
    					<label>Unique identifier</label>
    					<div class="content"><strong><?php echo $sitetree->route_name ?></strong></div>
    				</div>
          </div>
          
  				<?php foreach ($form as $idx => $widget):
            if (!$widget->isHidden()) : ?>
              <?php if ($culture == $idx) : // embedded translation form ?>
              
                <?php foreach ($widget as $idx2 => $tWidget) : 
                  if (!$tWidget->isHidden()) : ?>
              
                    <div class="sf_admin_form_row <?php if ($tWidget->hasError()) echo 'errors'; ?>">
                      <?php echo $tWidget->renderError(); ?>
                      <div>
                        <?php echo $tWidget->renderLabel(); ?>
                        <div class="content"><?php echo $tWidget->render(); ?></div>
                        <?php if ($help = $tWidget->renderHelp()) : ?><div class="help"><?php echo str_replace('<br />', '', $help); ?></div><?php endif; ?>
                      </div>
                    </div>
                
                  <?php endif;
                endforeach; ?>
              
              <?php else : ?>
            
                <div class="sf_admin_form_row <?php if ($widget->hasError()) echo 'errors'; ?>">
                  <?php echo $widget->renderError(); ?>
                  <div>
                    <?php echo $widget->renderLabel(); ?>
                    <div class="content"><?php echo $widget->render(); ?></div>
                    <?php if ($help = $widget->renderHelp()) : ?><div class="help"><?php echo str_replace('<br />', '', $help); ?></div><?php endif; ?>
                  </div>
                </div>
                
              <?php endif;
            endif; 
          endforeach; ?>
    		</fieldset>
        
        <ul class="sf_admin_actions">
          <li class="sf_admin_action_list"><?php echo link_to('Back to list', 'sitetree/index'); ?></li>
          <li class="sf_admin_action_save"><input type="submit" value="Save"></li>
        </ul>
    	</form>
    </div>
  </div>
</div>