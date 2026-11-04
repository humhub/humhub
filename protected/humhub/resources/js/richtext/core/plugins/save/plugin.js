import { Plugin } from 'prosemirror-state';

const savePlugin = (context) => {
    return new Plugin({
        props: {
            handleKeyDown(view, event) {
                if(context.options.keySubmit === false) {
                    return;
                }

                if(event.ctrlKey && event.key === 's') {
                    event.preventDefault();
                    let $form = context.editor.$.closest('form');

                    if(!$form.length) {
                        return;
                    }

                    let $submit = $form.find('[type="submit"]');
                    if($submit.length) {
                        context.editor.$.trigger('focusout');
                        $submit.trigger('click');
                    }
                }
            },
        },
    });
};

export {savePlugin}