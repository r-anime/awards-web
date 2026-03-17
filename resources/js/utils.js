import { marked } from 'marked';

export const nomineeImage = (nominee) => {
        try {
            return `background-image: url(/storage/${nominee.image})`;        
        } catch (error) {
            console.log('Error with image url');
            console.log(nominee);
            throw error;            
        }
    };

export function markdownit (it) {
	if (!it) {
		return '';
	}

	const html = marked(it);

	// Add target and rel to all links
	return html.replace(/<a\b(?![^>]*\btarget=)([^>]*)>/gi, '<a target="_blank" rel="noopener"$1>');
}