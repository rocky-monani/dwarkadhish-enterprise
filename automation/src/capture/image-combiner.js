const sharp = require('sharp');
const logger = require('../utils/logger');

/**
 * Image Combiner module using sharp.
 * Vertically stitches two separate captured PNG buffers/files into a single clean PNG image.
 */
async function combineImagesVertically(imagePath1, imagePath2, outputPath) {
    try {
        logger.info(`Combining captured section images vertically...`, { imagePath1, imagePath2, outputPath });

        const img1 = sharp(imagePath1);
        const img2 = sharp(imagePath2);

        const meta1 = await img1.metadata();
        const meta2 = await img2.metadata();

        const canvasWidth = Math.max(meta1.width || 0, meta2.width || 0);
        const canvasHeight = (meta1.height || 0) + (meta2.height || 0);

        // Resize images if widths differ slightly to align edges cleanly
        const buf1 = await img1.resize({ width: canvasWidth, fit: 'contain', background: { r: 255, g: 255, b: 255, alpha: 1 } }).toBuffer();
        const buf2 = await img2.resize({ width: canvasWidth, fit: 'contain', background: { r: 255, g: 255, b: 255, alpha: 1 } }).toBuffer();

        await sharp({
            create: {
                width: canvasWidth,
                height: canvasHeight,
                channels: 4,
                background: { r: 255, g: 255, b: 255, alpha: 1 }
            }
        })
        .composite([
            { input: buf1, top: 0, left: 0 },
            { input: buf2, top: meta1.height || 0, left: 0 }
        ])
        .png({ quality: 100 })
        .toFile(outputPath);

        logger.info(`Images successfully stitched vertically to ${outputPath}`);
        return true;
    } catch (err) {
        logger.error('Failed to combine section images', { error: err.message, stack: err.stack });
        throw err;
    }
}

module.exports = {
    combineImagesVertically
};
