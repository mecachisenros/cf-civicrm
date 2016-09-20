<?php
/**
 * The processor meta data template.
 *
 * This is an optional template for displaying returned meta data from the
 * "processor" stage. It is repeated for every meta item.
 *
 * Optionally you can use the filter `caldera_forms_get_entry_meta_{processer_slug}`
 * to format the data and use the default table.
 *
 * @see https://github.com/Desertsnowman/cf-formprocessor-boilerplate
 *
 * @since 0.2
 */

?>
<div>
	<strong>{{meta_key}}</strong>: {{meta_value}}
</div>
