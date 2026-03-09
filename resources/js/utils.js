export const nomineeImage = (nominee) => {
        try {
            return `background-image: url(/storage/${nominee.image})`;        
        } catch (error) {
            console.log('Error with image url');
            console.log(nominee);
            throw error;            
        }
    };