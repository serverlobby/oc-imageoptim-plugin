<div class="lazyimage loading">
<img<?php foreach($attributes as $attr => $val): ?> <?= $attr ?>="<?= e($val, true) ?>"<?php endforeach; ?> src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="/>
</div>
<noscript>
<img<?php foreach($attributes as $attr => $val): ?> <?= str_replace('data-srcset','srcset', str_replace('data-src','src',$attr)) ?>="<?= e($val, true) ?>"<?php endforeach; ?> />
</noscript>