<div class="row template sticky">
	<?php echo $this->labelEx($model,'template'); ?>
	<?php echo $this->dropDownList($model,'template',$templates); ?>
	<div class="tooltip">
		Please select which set of the templates should be used to generated the code.
	</div>
	<?php echo $this->error($model,'template'); ?>
</div>

<div class="buttons">
	<?php echo CHtml::submitButton('Preview',array('name'=>'preview')); ?>

	<?php if($model->status===CCodeModel::STATUS_PREVIEW && !$model->hasErrors()): ?>
		<?php echo CHtml::submitButton('Generate',array('name'=>'generate')); ?>
	<?php endif; ?>
</div>

<?php if(!$model->hasErrors()): ?>
	<div class="feedback">
	<?php if($model->status===CCodeModel::STATUS_SUCCESS): ?>
		<div class="success">
			<?php echo $model->successMessage(); ?>
		</div>
	<?php elseif($model->status===CCodeModel::STATUS_ERROR): ?>
		<div class="error">
			<?php echo $model->errorMessage(); ?>
		</div>
	<?php endif; ?>

	<?php if(isset($_POST['generate'])): ?>
		<pre class="results"><?php echo $model->renderResults(); ?></pre>
	<?php elseif(isset($_POST['preview'])): ?>
		<?php echo CHtml::hiddenField("answers"); ?>
		<table class="preview">
			<tr>
				<th class="file">Code File</th>
				<th class="confirm">
					<label for="check-all">Generate</label>
					<?php
						$count=0;
						foreach($model->files as $file)
						{
							if($file->operation!==CCodeFile::OP_SKIP)
								$count++;
						}
						if($count>1)
							echo '<input type="checkbox" name="checkAll" id="check-all" />';
					?>
				</th>
			</tr>
			<?php foreach($model->files as $i=>$file): ?>
			<tr class="<?php echo $file->operation; ?>">
				<td class="file">
					<?php echo CHtml::link(CHtml::encode($file->relativePath), array('code','id'=>$i), array('class'=>'view-code','rel'=>$file->path)); ?>
					<?php if($file->operation===CCodeFile::OP_OVERWRITE): ?>
						(<?php echo CHtml::link('diff', array('diff','id'=>$i), array('class'=>'view-code','rel'=>$file->path)); ?>)
					<?php endif; ?>
				</td>
				<td class="confirm">
					<?php
					if($file->operation===CCodeFile::OP_SKIP)
						echo 'unchanged';
					else
					{
						$key=md5($file->path);
						echo CHtml::label($file->operation, "answers_{$key}")
							. ' ' . CHtml::checkBox("answers[$key]", $model->confirmed($file));
					}
					?>
				</td>
			</tr>
			<?php endforeach; ?>
		</table>
	<?php endif; ?>
	</div>
<?php endif; ?>
