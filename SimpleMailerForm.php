<?php

class SimpleMailerForm
{
	public $class = null;
	public $id = null;
	public $style = null;
	public $name = 'sm-form';
	public $enctype = null;
	public $charset = null;
	public $novalidate = null;
	public $action = '';
	public $content = null;
	public $method = 'post';

	public function set($key, $value) { $this->{$key} = $value; }

	public function add($content) {
		if(is_object($content)) $this->content .= $content->render();
		else $this->content .= $content;
	}

	public function render()
	{
		$markup = '<form';
		$markup .= (($this->class) ? ' class="'.$this->class.'"' : '');
		$markup .= (($this->id) ? ' id="'.$this->id.'"' : '');
		$markup .= (($this->style) ? ' style="'.$this->style.'"' : '');
		$markup .= (($this->name) ? ' name="'.$this->name.'"' : '');
		$markup .= (($this->charset) ? ' accept-charset="'.$this->charset.'"' : '');
		$markup .= (($this->enctype) ? ' enctype="'.$this->enctype.'"' : '');
		$markup .= (($this->novalidate) ? ' novalidate' : '');
		$markup .= ' action="'.$this->action.'"';
		$markup .= (($this->method) ? ' method="'.$this->method.'"' : '');
		$markup .= ">\r\n";
		$markup .= (($this->content) ? "$this->content</form>\r\n" : "</form>\r\n");
		return $markup;
	}
}


class SimpleMailerInput
{
	public $type = 'text';
	public $class = null;
	public $id = null;
	public $style = null;
	public $name = null;
	public $value = null;
	public $placeholder = null;
	public $required = null;
	public $size = null;
	public $maxlength = null;
	public $multiple = null;

	public $useFormWrapper = true;
	public $formWrapperClass = 'sm-form-wrapper';

	public function set($key, $value) { $this->{$key} = $value; }

	public function render()
	{
		$markup = '<input type="'.$this->type.'"';
		$markup .= (($this->class) ? ' class="'.$this->class.'"' : '');
		$markup .= (($this->id) ? ' id="'.$this->id.'"' : '');
		$markup .= (($this->style) ? ' style="'.$this->style.'"' : '');
		$markup .= (($this->name) ? ' name="'.$this->name.'"' : '');
		$markup .= (($this->value) ? ' value="'.$this->value.'"' : '');
		$markup .= (($this->maxlength) ? ' maxlength="'.$this->maxlength.'"' : '');
		$markup .= (($this->size) ? ' size="'.$this->size.'"' : '');
		$markup .= (($this->placeholder) ? ' placeholder="'.$this->placeholder.'"' : '');
		$markup .= (($this->multiple) ? ' multiple="multiple"' : '');
		$markup .= (($this->required) ? ' required="required"' : '');
		$markup .= ">\r\n";

		return $markup;
	}
}


class SimpleMailerFieldset
{
	public $class = null;
	public $id = null;
	public $style = null;
	public $legend = null;
	public $content = null;

	public function set($key, $value) { $this->{$key} = $value; }

	public function add($content) {
		if(is_object($content)) $this->content .= $content->render();
		else $this->content .= $content;
	}

	public function render()
	{
		$markup = '<fieldset';
		$markup .= (($this->class) ? ' class="'.$this->class.'"' : '');
		$markup .= (($this->id) ? ' id="'.$this->id.'"' : '');
		$markup .= (($this->style) ? ' style="'.$this->style.'"' : '');
		$markup .= ">\r\n";
		$markup .= (($this->legend) ? "<legend>$this->legend</legend>\r\n" : '');
		$markup .= (($this->content) ? "$this->content</fieldset>\r\n" : "</fieldset>\r\n");
		return $markup;
	}
}


class SimpleMailerLabel
{
	public $class = null;
	public $id = null;
	public $for = null;
	public $style = null;
	public $content = null;
	public $required = null;

	public function set($key, $value) { $this->{$key} = $value; }

	public function add($content) {
		if(is_object($content)) $this->content .= $content->render();
		else $this->content .= $content;
	}

	public function render()
	{
		$markup = '<label';
		$markup .= (($this->class) ? ' class="'.$this->class.'"' : '');
		$markup .= (($this->id) ? ' id="'.$this->id.'"' : '');
		$markup .= (($this->style) ? ' style="'.$this->style.'"' : '');
		$markup .= (($this->for) ? ' for="'.$this->for.'"' : '');
		$markup .= ">\r\n";
		$markup .= (($this->content) ? "$this->content</label>\r\n" : "</label>\r\n");


		return $markup;
	}
}


class SimpleMailerWrapper
{
	public $tag = 'div';
	public $class = 'form-group';
	public $id = null;
	public $style = null;
	public $content = null;

	public function set($key, $value) { $this->{$key} = $value; }

	public function add($content) {
		if(is_object($content)) $this->content .= $content->render();
		else $this->content .= $content;
	}

	public function render()
	{
		$markup = '<'.$this->tag;
		$markup .= (($this->class) ? ' class="'.$this->class.'"' : '');
		$markup .= (($this->id) ? ' id="'.$this->id.'"' : '');
		$markup .= (($this->style) ? ' style="'.$this->style.'"' : '');
		$markup .= ">\r\n";
		$markup .= (($this->content) ? "$this->content</$this->tag>\r\n" : "</$this->tag>\r\n");
		return $markup;
	}
}


class SimpleMailerTextarea
{
	public $class = null;
	public $id = null;
	public $style = null;
	public $rows = '10';
	public $cols = '60';
	public $name = null;
	public $placeholder = null;
	public $required = null;
	public $content = null;

	public function set($key, $value) { $this->{$key} = $value; }

	public function add($content) {
		//if(is_object($content)) $this->content .= $content->render();
		$this->content .= $content;
	}

	public function render()
	{
		$markup = '<textarea';
		$markup .= (($this->class) ? ' class="'.$this->class.'"' : '');
		$markup .= (($this->id) ? ' id="'.$this->id.'"' : '');
		$markup .= (($this->style) ? ' style="'.$this->style.'"' : '');
		$markup .= (($this->name) ? ' name="'.$this->name.'"' : '');
		$markup .= (($this->rows) ? ' rows="'.$this->rows.'"' : '');
		$markup .= (($this->cols) ? ' cols="'.$this->cols.'"' : '');
		$markup .= (($this->placeholder) ? ' placeholder="'.$this->placeholder.'"' : '');
		$markup .= (($this->required) ? ' required="required"' : '');
		$markup .= '>';
		$markup .= (($this->content) ? "$this->content</textarea>\r\n" : "</textarea>\r\n");
		return $markup;
	}
}


class SimpleMailerReCaptcha
{
	public $site_key = null;
	public $class = 'g-recaptcha';

	public function set($key, $value) { $this->{$key} = $value; }

	public function render() {
		return '<div '.(($this->class) ? ' class="'.$this->class.'" ' : '').'data-sitekey="'.$this->site_key.'"></div>';
	}
}


class SimpleMailerButton
{
	public $type = 'submit';
	public $class = null;
	public $id = null;
	public $style = null;
	public $name = null;
	public $content = null;

	public function set($key, $value) { $this->{$key} = $value; }

	public function add($content) { $this->content .= $content; }

	public function render()
	{
		$markup = '<button type="'.$this->type.'"';
		$markup .= (($this->class) ? ' class="'.$this->class.'"' : '');
		$markup .= (($this->id) ? ' id="'.$this->id.'"' : '');
		$markup .= (($this->style) ? ' style="'.$this->style.'"' : '');
		$markup .= (($this->name) ? ' name="'.$this->name.'"' : '');
		$markup .= '>';
		$markup .= (($this->content) ? "$this->content</button>\r\n" : "</button>\r\n");
		return $markup;
	}

}


class SimpleMailerJsBlocks
{
	public $button = '#submit';
	public $stopdelay = '#stop-delay';
	public $delaytext = null;
	public $formid = '#contact';
	public $successmsg = 'Submission was successful.';
	public $errormsg = 'An error occurred.';
	public $filefieldid = '#attachments';

	public function set($key, $value) { $this->{$key} = $value; }

	public function renderDelay()
	{
		ob_start(); ?>
		<div id="delay">
			<div id="clamp">
				<a id="stop-delay" href="#">&nbsp;</a>
				<span id="loader"></span><p id="delay-info" class="blink"><?php echo $this->delaytext; ?></p>
			</div>
		</div>
		<?php return ob_get_clean();
	}


	public function renderJs()
	{
		ob_start(); ?>
		<script>
		$("<?php echo $this->stopdelay; ?>").click(function(e) {
			e.preventDefault();
			$("#delay").fadeOut();
		});
		$(document).on({
			ajaxStart: function() { $("#delay").show(); },
			ajaxStop: function() { $("#delay").fadeOut();}
		});
		var frm = $("<?php echo $this->formid ?>");
		frm.submit(function(e) {
			e.preventDefault();
			//Declaring new Form Data Instance
			var formData = new FormData(this);
			$.ajax({
				dataType: "json",
				type: frm.attr("method"),
				url: frm.attr("action"),
				data: formData, //frm.serialize(),
				cache: false,
				contentType: false,
				processData: false,
				success: function (data) {
					//console.log(data);
					if(data && data.msgs) {
						if($('#sm-msgs-wrapper').length == 0) {
							$('<div id="sm-msgs-wrapper">'+data.msgs+"</div>").insertBefore('<?php
								echo $this->formid ?>');
						} else {
							$("#sm-msgs-wrapper").replaceWith('<div id="sm-msgs-wrapper">'+data.msgs+"</div>");
						}
						if(data.status == 1) { $("<?php echo $this->formid ?>")[0].reset();}
						var el = $("#sm-msgs-wrapper");
						var elOffset = el.offset().top;
						var elHeight = el.height();
						var windowHeight = $(window).height();
						var offset;

						if (elHeight < windowHeight) { offset = elOffset - ((windowHeight / 2) - (elHeight / 2)); }
						else { offset = elOffset; }
						$("html, body").animate({
							scrollTop: offset
						});
					}
				},
				error: function (data) {
					console.log("<?php echo $this->successmsg ?>");
					console.log(data);
				},
			});
		});
		</script>
		<?php return ob_get_clean();
	}
}