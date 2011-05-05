<?php
echo $echo;

echo form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=disqus');

$this->table->clear();

$cp_table_template['heading_cell_start'] = '<th style="width:50%">';

$this->table->set_template($cp_table_template);
$this->table->set_heading('Parameter', 'Value');

foreach( $settings as $field => $value )
{
	$tbl_param = '';
	$tbl_value = '';

	$tbl_param = lang($field);

		$tbl_value = form_input(array(
			'name' => $field,
			'id' => $field,
			'value' => $value,
			'size' => 50
		));


	$this->table->add_row(array($tbl_param, $tbl_value));

	// clean up
	$tbl_param = NULL;
	$tbl_value = NULL;
	unset($tbl_param, $tbl_value);
}

echo $this->table->generate();

echo form_submit(array('name'=>'submit', 'value'=>lang('submit'), 'class'=>'submit'));
echo form_close();
?>
