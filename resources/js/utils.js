import { marked } from 'marked';

export const nomineeImage = (nominee) => {
        try {
            const image = nominee.image || '/images/awardslogo.png';
            const isFullPath = ['http://', 'https://', '/images/', '/storage/'].some((prefix) => image.startsWith(prefix));
            const storagePath = image.startsWith('/') ? image.slice(1) : image;
            const imageUrl = isFullPath ? image : `/storage/${storagePath}`;
            return `background-image: url("${imageUrl}")`;
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