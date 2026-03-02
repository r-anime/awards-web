export const nomineeImage = (nominee) => {
        try {
            return `background-image: url(/${nominee.image})`;        
        } catch (error) {
            console.log('Error with image url');
            console.log(nominee);
            throw error;            
        }
    };