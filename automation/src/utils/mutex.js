/**
 * Simple Async Mutex Lock to queue simultaneous Playwright automation requests.
 * Prevents two requests from interfering with the same persistent browser context.
 */
class Mutex {
    constructor() {
        this.queue = [];
        this.isLocked = false;
    }

    acquire() {
        return new Promise((resolve) => {
            if (!this.isLocked) {
                this.isLocked = true;
                resolve(this.release.bind(this));
            } else {
                this.queue.push(resolve);
            }
        });
    }

    release() {
        if (this.queue.length > 0) {
            const nextResolve = this.queue.shift();
            nextResolve(this.release.bind(this));
        } else {
            this.isLocked = false;
        }
    }
}

const automationMutex = new Mutex();

module.exports = automationMutex;
