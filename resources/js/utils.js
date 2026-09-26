import { marked } from 'marked';

export const nomineeImage = (nominee) => {
        try {
            const image = nominee.image || '/images/awardslogo.png';
            const imageUrl = image.startsWith('http://') || image.startsWith('https://') || image.startsWith('/')
                ? image
                : `/storage/${image}`;
            return `background-image: url(${imageUrl})`;
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