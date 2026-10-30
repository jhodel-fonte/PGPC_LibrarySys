// Export the same locally generated QR displayed on screen, with a white quiet zone.
export async function saveQrImage(svgSource, filename = 'library-qr') {
    const documentSvg = new DOMParser().parseFromString(svgSource, 'image/svg+xml');
    const svg = documentSvg.documentElement;
    if (svg.localName !== 'svg' || documentSvg.querySelector('parsererror')) {
        throw new Error('The QR image is not ready. Please try again.');
    }
    if (!svg.hasAttribute('viewBox')) {
        const width = parseFloat(svg.getAttribute('width')) || 300;
        const height = parseFloat(svg.getAttribute('height')) || width;
        svg.setAttribute('viewBox', `0 0 ${width} ${height}`);
    }
    svg.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
    svg.setAttribute('width', '1024');
    svg.setAttribute('height', '1024');
    const sourceUrl = URL.createObjectURL(new Blob([new XMLSerializer().serializeToString(svg)], { type: 'image/svg+xml' }));
    try {
        const image = new Image();
        await new Promise((resolve, reject) => {
            image.onload = resolve;
            image.onerror = () => reject(new Error('Could not prepare the QR image. Please try again.'));
            image.src = sourceUrl;
        });
        const canvas = document.createElement('canvas');
        canvas.width = canvas.height = 1024;
        const context = canvas.getContext('2d');
        if (!context) throw new Error('Your browser cannot save this image. Try another browser.');
        context.fillStyle = '#ffffff';
        context.fillRect(0, 0, 1024, 1024);
        context.drawImage(image, 64, 64, 896, 896);
        const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/png'));
        if (!blob) throw new Error('Could not save the QR image. Please try again.');
        const downloadUrl = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = (String(filename).replace(/[^a-zA-Z0-9_-]/g, '_').slice(0, 100) || 'library-qr') + '.png';
        document.body.appendChild(link);
        link.click();
        link.remove();
        setTimeout(() => URL.revokeObjectURL(downloadUrl), 60000);
    } finally {
        URL.revokeObjectURL(sourceUrl);
    }
}
