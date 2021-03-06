<h1>Notes</h1>

<div id="notes">
<?php if ($timeline): ?>
<ul>
	<?php foreach($timeline as $note): ?>
	<li class="note">
		<span class="note_text"><?= item_linkify($note->content, 'twitter') ?></span>
		<span class="note_details"><a href="<?= base_url().'notes/'.$note->content_id ?>"><?= format_datetime('MONTH_DAY_YEAR_TIME_ABBR', $note->created_at) ?></a></span>
	</li>
	<?php endforeach; ?>
</ul>
<?php else: ?>
<p>No notes published yet</p>
<?php endif; ?>
</div>
