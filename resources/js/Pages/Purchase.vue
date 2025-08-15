<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import DangerButton from "@/Components/DangerButton.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import WarningButton from "@/Components/WarningButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Modal from "@/Components/Modal.vue";
import Swal from "sweetalert2";
import { nextTick, ref, computed, watch } from "vue";

const nameInput = ref(null);
const modal = ref(false);
const title = ref("");
const operation = ref(1);
const id = ref("");

const props = defineProps({
    purchases: { type: Object },
    inventories: { type: Object },
});
const form = useForm({
    product: "",
    quantity: "",
    price: "",
    status: "Pending",
});
const formPage = useForm({});
const onPageClick = (event) => {
    formPage.get(route("purchases.index", { page: event }));
};
const openModal = (op, product, quantity, price, status, purchase) => {
    modal.value = true;
    nextTick(() => nameInput.value.focus());
    operation.value = op;
    id.value = purchase;
    if (op == 1) {
        title.value = "Add Entry";
    } else {
        title.value = "Edit Entry";
        form.product = product;
        form.quantity = quantity;
        form.price = price;
        form.status = status;
    }
};
const closeModal = () => {
    modal.value = false;
    form.reset();
};

const save = () => {
    if (operation.value == 1) {
        form.post(route("purchase.store"), {
            onSuccess: () => {
                ok("Entry Added");
            },
        });
    } else {
        form.put(route("purchase.update", id.value), {
            onSuccess: () => {
                ok("Entry updated");
            },
        });
    }
};

const ok = (msj) => {
    form.reset();
    closeModal();
    Swal.fire({ title: msj, icon: "success" });
};
const deletePurchase = (purchase) => {
    const alerta = Swal.mixin({
        buttonStyling: true,
    });
    alerta
        .fire({
            title: "This action will delete " + purchase.product,
            icon: "exclamation",
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-check"></i> Confirm',
            cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancel',
        })
        .then((result) => {
            if (result.isConfirmed) {
                form.delete(route("purchase.destroy", purchase), {
                    onSuccess: () => {
                        ok("Entry deleted");
                    },
                });
            }
        });
};

const formatToIDR = (value) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
    }).format(value);
};

const productMap = computed(() => {
    const map = {};
    props.inventories.forEach((product) => {
        map[product.name] = product.sell_price;
    });
    return map;
});

watch(
    () => [form.product, form.quantity],
    ([product, quantity]) => {
        const basePrice = productMap.value[product] || 0;
        const qty = parseInt(quantity) || 0;
        form.price = basePrice * qty;
    }
);

const isEditMode = computed(() => title.value === "Edit Entry");

const updateStatus = (purchase, newStatus) => {
    // Kalau statusnya mau di-Approve, cek stok maksimal
    if (purchase.status === "Pending" && newStatus === "Approved") {
        // Cari data produk dari inventories
        const productData = props.inventories.find(p => p.name === purchase.product);

        if (productData) {
            const totalPlannedQty = productData.base_qty + productData.in_demand_qty + purchase.quantity;

            if (totalPlannedQty > 200) {
                const allowedQty = 200 - (productData.base_qty + productData.in_demand_qty);

                return Swal.fire({
                    title: "Stok Melebihi Batas",
                    text: `Stok akan melebihi 200. Quantity akan diubah menjadi ${allowedQty}. Lanjutkan?`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Ya, Sesuaikan",
                    cancelButtonText: "Batal"
                }).then(result => {
                    if (result.isConfirmed) {
                        // Set quantity ke batas maksimal yang diizinkan
                        purchase.quantity = allowedQty;

                        // Kirim request update
                        sendStatusUpdate(purchase, newStatus);
                    }
                });
            }
        }
    }

    // Kalau bukan approve atau tidak melebihi batas
    sendStatusUpdate(purchase, newStatus);
};

// Fungsi terpisah untuk kirim PUT request
function sendStatusUpdate(purchase, newStatus) {
    const statusForm = useForm({
        product: purchase.product,
        quantity: purchase.quantity,
        price: purchase.price,
        status: newStatus,
    });

    statusForm.put(route("purchase.update", purchase.id), {
        onSuccess: () => {
            purchase.status = newStatus;
            Swal.fire({
                title: `${newStatus} successfully!`,
                icon: "success",
            });
        },
        onError: () => {
            let errorMessage =
                Object.values(statusForm.errors)[0] ||
                "Error updating status";

            Swal.fire({
                title: "Error",
                text: errorMessage,
                icon: "error",
            });
        },
    });
}

const dateSortOrder = ref("asc");

const toggleDateSort = () => {
    dateSortOrder.value = dateSortOrder.value === "asc" ? "desc" : "asc";
};

const startDate = ref(null); // format: '2025-08-01'
const endDate = ref(null);
const searchQuery = ref("");
const statusFilter = ref("");

const filteredPurchase = computed(() => {
    return props.purchases
        .filter((purchase) => {
            const createdAt = new Date(purchase.created_at);
            const query = searchQuery.value.toLowerCase();

            const matchesSearch =
                purchase.product.toLowerCase().includes(query) ||
                purchase.status.toLowerCase().includes(query) ||
                purchase.po_number?.toLowerCase().includes(query) ||
                createdAt.toLocaleDateString().includes(searchQuery.value);

            const matchesStatus =
                statusFilter.value === "" ||
                purchase.status === statusFilter.value;

            const matchesDate =
                (!startDate.value || createdAt >= new Date(startDate.value)) &&
                (!endDate.value ||
                    createdAt <= new Date(endDate.value + "T23:59:59"));

            return matchesSearch && matchesStatus && matchesDate;
        })
        .sort((a, b) => {
            const dateA = new Date(a.created_at);
            const dateB = new Date(b.created_at);
            return dateSortOrder.value === "asc"
                ? dateA - dateB
                : dateB - dateA;
        });
});

// ✅ Reset function diletakkan DI LUAR computed
function resetFilters() {
    startDate.value = null;
    endDate.value = null;
    searchQuery.value = "";
    statusFilter.value = "";
}

const statusColors = {
    Pending: "bg-yellow-100 text-yellow-800",
    Approved: "bg-blue-100 text-blue-800",
    Paid: "bg-indigo-100 text-indigo-800",
    Shipped: "bg-purple-100 text-purple-800",
    Received: "bg-green-100 text-green-800",
    Completed: "bg-gray-100 text-gray-800",
    Canceled: "bg-red-100 text-red-800",
    Refunded: "bg-pink-100 text-pink-800",
};

const vendorInfoMap = computed(() => {
    const map = {};
    props.inventories.forEach((product) => {
        map[product.name] = {
            vendor_name: product.vendor_name || "-",
            vendor_contact: product.vendor_contact || "-",
        };
    });
    return map;
});

const paidModal = ref(false);
const selectedPurchase = ref(null);
const paymentFile = ref(null);
const paymentProofDesc = ref("");

const openPaidModal = (purchase) => {
    selectedPurchase.value = purchase;
    paidModal.value = true;
};

const closePaidModal = () => {
    paidModal.value = false;
    paymentFile.value = null;
};

const onFileChange = (event) => {
    paymentFile.value = event.target.files[0];
};

const submitPaid = () => {
    if (!paymentFile.value) {
        Swal.fire("Error", "Please upload a payment proof first.", "error");
        return;
    }

    const formData = new FormData();
    formData.append("product", selectedPurchase.value.product);
    formData.append("quantity", selectedPurchase.value.quantity);
    formData.append("price", selectedPurchase.value.price);
    formData.append("status", "Paid");
    formData.append("payment_proof", paymentFile.value);
    formData.append("payment_proof_desc", paymentProofDesc.value);

    axios.post(route("purchase.update", selectedPurchase.value.id), formData, {
        headers: { "Content-Type": "multipart/form-data" },
        params: { _method: "PUT" }
    })
    .then(() => {
        closePaidModal();
        Swal.fire("Success", "Purchase marked as Paid!", "success").then(() => {
            location.reload();
        });
    })
    .catch(err => {
        Swal.fire("Error", err.response.data.message || "Upload failed", "error");
    });
};


const receivedModal = ref(false);
const selectedReceivedPurchase = ref(null);
const receivedFile = ref(null);
const receivedProofDesc = ref("");

const openReceivedModal = (purchase) => {
    selectedReceivedPurchase.value = purchase;
    receivedModal.value = true;
};

const closeReceivedModal = () => {
    receivedModal.value = false;
    receivedFile.value = null;
    receivedProofDesc.value = "";
};

const onReceivedFileChange = (event) => {
    receivedFile.value = event.target.files[0];
};

const submitReceived = () => {
    if (!receivedFile.value) {
        Swal.fire("Error", "Please upload a received proof first.", "error");
        return;
    }

    const formData = new FormData();
    formData.append("product", selectedReceivedPurchase.value.product);
    formData.append("quantity", selectedReceivedPurchase.value.quantity);
    formData.append("price", selectedReceivedPurchase.value.price);
    formData.append("status", "Received");
    formData.append("received_proof", receivedFile.value);
    formData.append("received_proof_desc", receivedProofDesc.value);

    axios.post(route("purchase.update", selectedReceivedPurchase.value.id), formData, {
        headers: { "Content-Type": "multipart/form-data" },
        params: { _method: "PUT" }
    })
    .then(() => {
        closeReceivedModal();
        Swal.fire("Success", "Purchase marked as Received!", "success").then(() => {
            location.reload();
        });
    })
    .catch(err => {
        Swal.fire("Error", err.response.data.message || "Upload failed", "error");
    });
};


const detailModal = ref(false);
const detailData = ref(null);

const openDetailModal = (purchase) => {
    detailData.value = purchase;
    detailModal.value = true;
};

const closeDetailModal = () => {
    detailModal.value = false;
    detailData.value = null;
};



</script>

<template>
    <Head title="Purchase" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Purchasing Module
            </h2>
        </template>

        <div
            class="py-12"
            v-if="
                $page.props.auth.user.role === 'Purchase' ||
                $page.props.auth.user.role === 'Admin'
            "
        >
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Pesan untuk role Purchase -->
                        <div v-if="$page.props.auth.user.role === 'Purchase'">
                            <p>Welcome, You're logged in as Purchasing</p>
                        </div>

                        <!-- Tombol Add untuk Admin dan Purchase -->
                        <div
                            v-if="
                                $page.props.auth.user.role === 'Admin' ||
                                $page.props.auth.user.role === 'Purchase'
                            "
                            class="mt-4"
                        >
                            <PrimaryButton @click="($event) => openModal(1)">
                                Add
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="m-6 flex flex-wrap gap-4 items-center bg-white p-4 rounded-lg shadow-md max-w-xl mx-auto"
            >
                <input
                    type="date"
                    v-model="startDate"
                    class="input input-bordered"
                />
                <input
                    type="date"
                    v-model="endDate"
                    class="input input-bordered"
                />
                <button @click="resetFilters" class="btn btn-outline btn-error">
                    Reset Filter
                </button>

                <input
                    type="text"
                    placeholder="Search by product, status, or date..."
                    v-model="searchQuery"
                    class="flex-grow min-w-[200px] border border-gray-300 rounded-lg px-4 py-2 text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-50 0 transition"
                />

                <select
                    v-model="statusFilter"
                    class="w-40 border border-gray-300 rounded-lg px-4 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                >
                    <option value="">All Statuses</option>
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Paid">Paid</option>
                    <option value="Received">Received</option>
                    <option value="Completed">Completed</option>
                    <option value="Canceled">Canceled</option>
                    <option value="Deleted">Deleted</option>
                </select>
            </div>

            <div class="mt-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="relative overflow-x-auto my-6 shadow rounded-lg">
                    <table class="min-w-full text-sm text-left text-gray-600">
                        <thead
                            class="bg-gray-100 text-xs uppercase text-gray-600 border-b border-gray-300"
                        >
                            <tr>
                                <th scope="col" class="px-4 py-3">-</th>
                                <th scope="col" class="px-4 py-3">No</th>
                                <th
                                    scope="col"
                                    class="px-4 py-3 cursor-pointer select-none"
                                    @click="toggleDateSort"
                                >
                                    Date
                                    <span v-if="dateSortOrder === 'asc'"
                                        >▲</span
                                    >
                                    <span v-else>▼</span>
                                </th>
                                <th scope="col" class="px-4 py-3">Product</th>
                                <th scope="col" class="px-4 py-3">
                                    Vendor Name
                                </th>
                                <th scope="col" class="px-4 py-3">
                                    Vendor Contact
                                </th>
                                <th scope="col" class="px-4 py-3 text-center">
                                    Quantity
                                </th>
                                <th scope="col" class="px-4 py-3 text-right">
                                    Price
                                </th>
                                <th scope="col" class="px-4 py-3">Status</th>
                                <th scope="col" class="px-4 py-3"></th>
                                <th scope="col" class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(inv, i) in filteredPurchase"
                                :key="inv.id"
                                :class="[
                                    'bg-white border-b last:border-none hover:bg-gray-50 transition',
                                    inv.status === 'Deleted'
                                        ? 'opacity-50 line-through'
                                        : '',
                                ]"
                            >
                                <td class="px-4 py-3 flex justify-center">
                                    <PrimaryButton
                                        class="px-2 py-1 text-xs"
                                        @click="openDetailModal(inv)"
                                    >
                                        📄 Details
                                    </PrimaryButton>
                                </td>

                                <td class="px-4 py-3 font-mono text-sm">
                                    {{ inv.po_number ?? "N/A" }}
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap">
                                    {{
                                        new Date(
                                            inv.created_at
                                        ).toLocaleDateString()
                                    }}
                                </td>
                                <th
                                    scope="row"
                                    class="px-4 py-3 font-semibold text-gray-900 whitespace-nowrap"
                                >
                                    {{ inv.product }}
                                </th>
                                <td class="px-4 py-3">
                                    {{
                                        vendorInfoMap[inv.product]
                                            ?.vendor_name || "-"
                                    }}
                                </td>
                                <td class="px-4 py-3">
                                    {{
                                        vendorInfoMap[inv.product]
                                            ?.vendor_contact || "-"
                                    }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    {{ inv.quantity }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono">
                                    {{ formatToIDR(inv.price) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span
                                        :class="[
                                            'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold',
                                            statusColors[inv.status],
                                        ]"
                                    >
                                        {{ inv.status }}
                                    </span>
                                </td>
                                
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <template v-if="inv.status === 'Pending'">
                                        <PrimaryButton
                                            class="px-3 py-1 text-sm"
                                            @click="
                                                () =>
                                                    updateStatus(
                                                        inv,
                                                        'Approved'
                                                    )
                                            "
                                        >
                                            ✔ Approve
                                        </PrimaryButton>
                                    </template>

                                    <template
                                        v-else-if="inv.status === 'Approved'"
                                    >
                                        <PrimaryButton
                                            class="px-3 py-1 text-sm"
                                            @click="openPaidModal(inv)"
                                        >
                                            💰 Paid
                                        </PrimaryButton>
                                        <DangerButton
                                            class="ml-2 px-3 py-1 text-sm"
                                            @click="
                                                () =>
                                                    updateStatus(
                                                        inv,
                                                        'Canceled'
                                                    )
                                            "
                                            >✖ Cancel</DangerButton
                                        >
                                    </template>

                                    <template v-else-if="inv.status === 'Paid'">
                                        <PrimaryButton
                                            class="px-3 py-1 text-sm"
                                            @click="openReceivedModal(inv)"
                                        >
                                            📥 Received
                                        </PrimaryButton>
                                    </template>

                                    <template v-else-if="inv.status === 'Received'">
                                        <PrimaryButton
                                        @click="() => updateStatus(inv, 'Completed')"
                                        class="px-3 py-1 text-sm"
                                        >✅ Complete</PrimaryButton>
                                    </template>

                                    <template v-else>
                                        <PrimaryButton
                                            disabled
                                            class="px-3 py-1 text-sm"
                                        >
                                            {{ inv.status }}
                                        </PrimaryButton>
                                    </template>
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap">
                                    <DangerButton
                                        v-if="
                                            !(
                                                inv.status === 'Deleted' ||
                                                inv.status === 'Approved' ||
                                                inv.status === 'Paid'
                                            )
                                        "
                                        class="ml-2 px-3 py-1 text-sm"
                                        @click="
                                            () => updateStatus(inv, 'Deleted')
                                        "
                                        >Delete</DangerButton
                                    >
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                </div>
            </div>
        </div>

        <div class="py-12" v-else>
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        You're not authorized here...
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="detailModal" @close="closeDetailModal">
            <h2 class="p-3 text-lg font-medium text-gray-900">Purchase Detail</h2>
            <div v-if="detailData" class="p-4 space-y-2 text-gray-700">
                <p><strong>PO Number:</strong> {{ detailData.po_number }}</p>
                <p><strong>Date:</strong> {{ new Date(detailData.created_at).toLocaleString() }}</p>
                <p><strong>Product:</strong> {{ detailData.product }}</p>
                <p><strong>Quantity:</strong> {{ detailData.quantity }}</p>
                <p><strong>Price:</strong> {{ formatToIDR(detailData.price) }}</p>
                <p><strong>Status:</strong> {{ detailData.status }}</p>
                <p><strong>Vendor Name:</strong> {{ vendorInfoMap[detailData.product]?.vendor_name || "-" }}</p>
                <p><strong>Vendor Contact:</strong> {{ vendorInfoMap[detailData.product]?.vendor_contact || "-" }}</p>

                <div class="mt-4">
                    <p class="font-semibold">Payment Proof:</p>
                    <template v-if="detailData.payment_proof">
                        <img
                            :src="`/storage/payment_proofs/${detailData.payment_proof}`"
                            alt="Payment Proof"
                            class="w-64 rounded shadow"
                        />
                        <p class="mt-2 text-gray-600">
                            <strong>Description:</strong> {{ detailData.payment_proof_desc || '-' }}
                        </p>
                    </template>
                    <template v-else>
                        <p class="text-red-500 italic">Not Paid Yet</p>
                    </template>
                </div>

                <div class="mt-4">
                    <p class="font-semibold">Received Proof:</p>
                    <template v-if="detailData.received_proof">
                        <img
                            :src="`/storage/received_proofs/${detailData.received_proof}`"
                            alt="Received Proof"
                            class="w-64 rounded shadow"
                        />
                        <p class="mt-2 text-gray-600">
                            <strong>Description:</strong> {{ detailData.received_proof_desc || '-' }}
                        </p>
                    </template>
                    <template v-else>
                        <p class="text-red-500 italic">Not Received Yet</p>
                    </template>
                </div>


            </div>
            <div class="p-3 mt-6">
                <PrimaryButton @click="closeDetailModal">Close</PrimaryButton>
            </div>
        </Modal>


        <Modal :show="paidModal" @close="closePaidModal">
            <h2 class="p-3 text-lg font-medium text-gray-900">Upload Payment Proof</h2>
            <div class="p-3">
                <input type="file" @change="onFileChange" accept="image/*" />

                <textarea
                    v-model="paymentProofDesc"
                    placeholder="Enter payment description"
                    class="mt-3 w-full border border-gray-300 rounded-lg px-3 py-2"
                ></textarea>
            </div>
            <div class="p-3 mt-6">
                <PrimaryButton :disabled="!paymentFile" @click="submitPaid">
                    Submit Payment
                </PrimaryButton>
            </div>
        </Modal>

        <Modal :show="receivedModal" @close="closeReceivedModal">
            <h2 class="p-3 text-lg font-medium text-gray-900">Upload Received Proof</h2>
            <div class="p-3">
                <input type="file" @change="onReceivedFileChange" accept="image/*" />
                <textarea
                    v-model="receivedProofDesc"
                    placeholder="Enter received proof description"
                    class="mt-3 w-full border border-gray-300 rounded-lg px-3 py-2"
                ></textarea>
            </div>
            <div class="p-3 mt-6">
                <PrimaryButton :disabled="!receivedFile" @click="submitReceived">
                    Submit Received Proof
                </PrimaryButton>
            </div>
        </Modal>



        <Modal :show="modal" @close="closeModal">
            <h2 class="p-3 text-lg font.medium text-gray-900">{{ title }}</h2>
            <div class="p-3 mt-6">
                <InputLabel for="product" value="Product"></InputLabel>
                <select
                    id="product"
                    ref="nameInput"
                    v-model="form.product"
                    :disabled="isEditMode"
                    class="mt-1 block w-3/4 rounded-md border-gray-300 shadow-sm focus:ring focus:ring-indigo-200"
                >
                    <option value="" disabled>Select a product</option>
                    <option
                        v-for="product in props.inventories"
                        :key="product.id"
                        :value="product.name"
                    >
                        {{ product.name }}
                    </option>
                </select>
                <InputError
                    :message="form.errors.product"
                    class="mt-2"
                ></InputError>
            </div>
            <div class="p-3">
                <InputLabel for="quantity" value="Quantity"></InputLabel>
                <TextInput
                    id="quantity"
                    ref="nameInput"
                    v-model="form.quantity"
                    type="text"
                    class="mt-1 block w-3/4"
                    placeholder="Quantity"
                    :readonly="isEditMode"
                ></TextInput>
                <InputError
                    :message="form.errors.quantity"
                    class="mt-2"
                ></InputError>
            </div>
            <div class="p-3">
                <InputLabel for="price" value="Price"></InputLabel>
                <TextInput
                    id="price"
                    ref="nameInput"
                    v-model="form.price"
                    type="text"
                    class="mt-1 block w-3/4"
                    placeholder="Price"
                    :readonly="true"
                />
                <InputError
                    :message="form.errors.price"
                    class="mt-2"
                ></InputError>
            </div>
            <div class="p-3 hidden">
                <InputLabel for="status" value="Status"></InputLabel>
                <select id="status" v-model="form.status">
                    <option value="Pending">Pending</option>
                </select>
            </div>
            <div class="p-3 mt-6">
                <PrimaryButton :disabled="form.processing" @click="save"
                    >Save</PrimaryButton
                >
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
